<?php

namespace Tests\Feature;

use App\Models\ChallanPayment;
use App\Models\FeeChallan;
use App\Models\GeneralSetting;
use App\Models\OneLinkLog;
use App\Models\Parant;
use App\Models\Student;
use App\Support\OneLinkAmount;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OneLinkTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-token-123';
    private const PREFIX = '10045';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'onelink.enabled'        => true,
            'onelink.token'          => self::TOKEN,
            'onelink.allowed_ips'    => ['127.0.0.1'],
            'onelink.institute_code' => '123456',
            'onelink.institute_name' => 'DPS Test School',
            'onelink.channel'        => 1,
            'onelink.bank_mnemonic'  => '',
            'onelink.amount_format'  => 'plain',
            'onelink.timezone'       => 'Asia/Karachi',
        ]);

        GeneralSetting::create([
            'name' => 'DPS', 'account_no' => '123', 'one_bill_prefix' => self::PREFIX, 'late_fee_fine' => 500,
        ]);

        // "today" in Pakistan = 2026-01-15 (due date)
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 10, 0, 0, 'Asia/Karachi'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeChallan(array $overrides = [], string $gender = 'Male'): FeeChallan
    {
        $parent = Parant::create([
            'father_name' => 'Ahmed Khan', 'father_nic' => (string) random_int(1000000000000, 9999999999999),
            'password' => bcrypt('x'), 'occupation' => 'x', 'address' => 'x',
            'contact_no' => (string) random_int(10000000000, 99999999999),
        ]);
        $student = Student::create([
            'parent_id' => $parent->id, 'name' => 'Ali', 'date_of_birth' => '2015-01-01',
            'gender' => $gender, 'status' => 'active',
        ]);

        return FeeChallan::create(array_merge([
            'student_id' => $student->id, 'month' => '2026-01', 'status' => 'pending',
            'issue_date' => '2026-01-01', 'due_date' => '2026-01-15',
            'total_amount' => 25000, 'amount_after_due_date' => 25500,
        ], $overrides));
    }

    private function inquiry(string $consumer, array $headers = [])
    {
        return $this->withHeaders(array_merge(['Authorization' => 'Bearer ' . self::TOKEN], $headers))
            ->postJson('/api/onelink/inquiry', [
                'consumer_number' => $consumer, 'bank_mnemonic' => 'BOP', 'reserved' => '', 'channel' => 1,
            ]);
    }

    private function pay(string $consumer, $amount, string $tranId = 'AUTH1', string $date = '20260115', string $time = '143000')
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->postJson('/api/onelink/payment', [
                'consumer_number' => $consumer, 'bank_mnemonic' => 'BOP', 'transaction_amount' => $amount,
                'tran_auth_id' => $tranId, 'tran_date' => $date, 'tran_time' => $time,
                'reserved' => '', 'channel' => 1,
            ]);
    }

    // ---------- challan numbers ----------

    public function test_new_challans_get_fixed_length_sequential_numbers(): void
    {
        $a = $this->makeChallan();
        $b = $this->makeChallan(['month' => '2026-02']);

        $this->assertSame(FeeChallan::formatChallanNo($a->id), $a->fresh()->challan_no);
        $this->assertSame(8, strlen($a->fresh()->challan_no));
        $this->assertStringStartsWith('1', $a->fresh()->challan_no);
        $this->assertNotSame($a->fresh()->challan_no, $b->fresh()->challan_no);
        $this->assertSame(self::PREFIX . $a->fresh()->challan_no, $a->fresh()->consumer_number);
    }

    // ---------- auth ----------

    public function test_rejects_bad_token(): void
    {
        $c = $this->makeChallan();
        $this->withHeaders(['Authorization' => 'Bearer wrong'])
            ->postJson('/api/onelink/inquiry', ['consumer_number' => $c->fresh()->challan_no, 'bank_mnemonic' => 'BOP', 'channel' => 1])
            ->assertOk()->assertJson(['response_code' => '01']);
    }

    public function test_rejects_ip_not_whitelisted(): void
    {
        config(['onelink.allowed_ips' => ['10.0.0.0/24']]);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertOk()->assertJson(['response_code' => '01', 'reserved' => 'Unauthorized source']);
    }

    public function test_cidr_whitelist_allows(): void
    {
        config(['onelink.allowed_ips' => ['127.0.0.0/8']]);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertJson(['response_code' => '00']);
    }

    public function test_disabled_service_returns_05(): void
    {
        config(['onelink.enabled' => false]);
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000)->assertJson(['response_Code' => '05']);
    }

    public function test_bank_mnemonic_enforced_when_configured(): void
    {
        config(['onelink.bank_mnemonic' => 'HBL']);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertJson(['response_code' => '01']);
    }

    // ---------- inquiry ----------

    public function test_inquiry_unpaid_on_due_date(): void
    {
        $c = $this->makeChallan();
        $this->inquiry(self::PREFIX . $c->fresh()->challan_no)
            ->assertOk()
            ->assertExactJson([
                'response_code' => '00',
                'institute_code' => '123456',
                'institute_name' => 'DPS Test School',
                'one_link_channel' => 1,
                'consumer_detail' => 'Ali S/O Ahmed Khan',
                'bill_status' => 'U',
                'due_date' => '20260115',
                'amount_within_dueDate' => '25000',
                'amount_after_dueDate' => '25500',
                'amount_receivable' => '25000',
                'late_fee' => '500.00',
                'billing_month' => '202601',
                'tran_auth_Id' => '',
            ]);
    }

    public function test_inquiry_after_due_date_includes_late_fee_and_daughter(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 16, 0, 30, 0, 'Asia/Karachi'));
        $c = $this->makeChallan([], 'Female');
        $this->inquiry($c->fresh()->challan_no)
            ->assertJson(['amount_receivable' => '25500', 'consumer_detail' => 'Ali D/O Ahmed Khan']);
    }

    public function test_due_date_uses_pakistan_time_not_utc(): void
    {
        // 2026-01-16 01:00 PKT = 2026-01-15 20:00 UTC -> already late in Pakistan
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 20, 0, 0, 'UTC'));
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertJson(['amount_receivable' => '25500']);
    }

    public function test_inquiry_unknown_consumer(): void
    {
        $this->inquiry('99999999')->assertJson(['response_code' => '01']);
    }

    public function test_inquiry_missing_fields(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->postJson('/api/onelink/inquiry', ['bank_mnemonic' => 'BOP'])
            ->assertJson(['response_code' => '03']);
    }

    public function test_inquiry_finds_old_short_challan_numbers_even_zero_padded(): void
    {
        $c = $this->makeChallan(['challan_no' => '4521']);
        $this->inquiry('0000' . '4521')->assertJson(['response_code' => '00', 'bill_status' => 'U']);
        $this->inquiry(self::PREFIX . '4521')->assertJson(['response_code' => '00']);
    }

    public function test_review_status_is_still_payable(): void
    {
        $c = $this->makeChallan(['status' => 'review']);
        ChallanPayment::create(['fee_challan_id' => $c->id, 'bank_name' => 'HBL', 'voucher_image' => 'upload/x.jpg']);

        $this->inquiry($c->fresh()->challan_no)->assertJson(['bill_status' => 'U']);
        $this->pay($c->fresh()->challan_no, 25000)->assertJson(['response_Code' => '00']);

        $p = $c->fresh()->payment;
        $this->assertSame('onelink', $p->source);
        $this->assertSame('upload/x.jpg', $p->voucher_image); // slip kept
    }

    public function test_soft_deleted_challan_not_found(): void
    {
        $c = $this->makeChallan();
        $no = $c->fresh()->challan_no;
        $c->delete();
        $this->inquiry($no)->assertJson(['response_code' => '01']);
    }

    // ---------- payment ----------

    public function test_successful_payment_marks_challan_paid(): void
    {
        $c = $this->makeChallan();
        $this->pay(self::PREFIX . $c->fresh()->challan_no, 25000)
            ->assertOk()
            ->assertExactJson([
                'response_Code' => '00',
                'institute_code' => '123456',
                'institute_name' => 'DPS Test School',
                'one_link_channel' => 1,
                'Identification_parameter' => 'Ali S/O Ahmed Khan',
                'reserved' => 'Thank you for the payment.',
            ]);

        $c->refresh()->load('payment');
        $this->assertSame('approved', $c->status);
        $this->assertSame('onelink', $c->payment->source);
        $this->assertSame('AUTH1', $c->payment->tran_auth_id);
        $this->assertSame('2026-01-15', $c->payment->paid_date->toDateString());
        $this->assertNull($c->payment->voucher_image);

        $this->inquiry($c->challan_no)->assertJson([
            'bill_status' => 'P', 'date_paid' => '20260115', 'tran_auth_Id' => 'AUTH1',
        ]);
    }

    public function test_payment_amount_mismatch(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25500)
            ->assertJson(['response_Code' => '05', 'reserved' => 'Amount entered does not match the unpaid amount']);
        $this->assertSame('pending', $c->fresh()->status);
    }

    public function test_payment_uses_bank_transaction_date_for_late_fee(): void
    {
        // Reaches us after midnight, but bank transaction happened on the due date.
        Carbon::setTestNow(Carbon::create(2026, 1, 16, 0, 5, 0, 'Asia/Karachi'));
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH9', '20260115', '235930')->assertJson(['response_Code' => '00']);
    }

    public function test_late_payment_requires_late_amount(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'A1', '20260116')->assertJson(['response_Code' => '05']);
        $this->pay($c->fresh()->challan_no, 25500, 'A2', '20260116')->assertJson(['response_Code' => '00']);
    }

    public function test_already_paid_returns_03(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_Code' => '00']);
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH2')->assertJson(['response_Code' => '03', 'reserved' => 'Bill already paid']);
    }

    public function test_retry_with_same_tran_auth_id_is_idempotent(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_Code' => '00']);
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_Code' => '00']);
        $this->assertSame(1, ChallanPayment::where('tran_auth_id', 'AUTH1')->count());
    }

    public function test_same_tran_auth_id_on_another_challan_rejected(): void
    {
        $a = $this->makeChallan();
        $b = $this->makeChallan(['month' => '2026-02']);
        $this->pay($a->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_Code' => '00']);
        $this->pay($b->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_Code' => '05', 'reserved' => 'Duplicate transaction ID']);
        $this->assertSame('pending', $b->fresh()->status);
    }

    public function test_payment_missing_fields(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->postJson('/api/onelink/payment', ['consumer_number' => '1'])
            ->assertJson(['response_Code' => '03']);
    }

    public function test_padded_amounts(): void
    {
        config(['onelink.amount_format' => 'padded']);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertJson(['amount_receivable' => '+0000002500000']);
        $this->pay($c->fresh()->challan_no, '+0000002500000')->assertJson(['response_Code' => '00']);
    }

    public function test_amount_parser(): void
    {
        $this->assertSame(27500.0, OneLinkAmount::parse('27500'));
        $this->assertSame(27500.5, OneLinkAmount::parse('27500.50'));
        $this->assertSame(27500.0, OneLinkAmount::parse('+0000002750000'));
        $this->assertSame(27500.0, OneLinkAmount::parse(27500));
        $this->assertNull(OneLinkAmount::parse('abc'));
        $this->assertSame('27500.50', OneLinkAmount::plain(27500.5));
    }

    public function test_every_request_is_logged(): void
    {
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no);
        $this->pay($c->fresh()->challan_no, 25000);
        $this->withHeaders(['Authorization' => 'Bearer bad'])->postJson('/api/onelink/inquiry', []);

        $this->assertSame(['inquiry', 'payment', 'rejected_inquiry'], OneLinkLog::orderBy('id')->pluck('type')->all());
        $this->assertSame($c->id, OneLinkLog::where('type', 'payment')->first()->fee_challan_id);
    }

    public function test_admin_cannot_revert_or_delete_onelink_paid_challan(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000)->assertJson(['response_Code' => '00']);

        $controller = app(\App\Http\Controllers\Backend\Admin\ChallanController::class);
        $controller->pendingChallan($c->id);
        $controller->destroyChallan($c->id);

        $this->assertSame('approved', $c->fresh()->status);
        $this->assertNull($c->fresh()->deleted_at);
    }
}
