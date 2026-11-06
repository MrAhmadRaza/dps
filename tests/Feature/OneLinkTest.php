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
            'onelink.bank_mnemonic'  => '',
            'onelink.rate_limit'     => 120,
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

    private function inquiry(string $consumer, array $headers = [], $channel = 1)
    {
        return $this->withHeaders(array_merge(['Authorization' => 'Bearer ' . self::TOKEN], $headers))
            ->postJson('/api/onelink/inquiry', [
                'consumer_number' => $consumer, 'bank_mnemonic' => 'BOP', 'channel' => $channel,
            ]);
    }

    private function pay(string $consumer, $amount, string $tranId = 'AUTH1', string $date = '20260115', string $time = '143000', $channel = 1)
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->postJson('/api/onelink/payment', [
                'consumer_number' => $consumer, 'bank_mnemonic' => 'BOP', 'transaction_amount' => $amount,
                'tran_auth_id' => $tranId, 'tran_date' => $date, 'tran_time' => $time, 'channel' => $channel,
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
            ->assertOk()->assertExactJson(['response_code' => '04', 'response_description' => 'Invalid credentials']);
    }

    public function test_rejects_ip_not_whitelisted(): void
    {
        config(['onelink.allowed_ips' => ['10.0.0.0/24']]);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertOk()->assertJson(['response_code' => '04']);
    }

    public function test_cidr_whitelist_allows(): void
    {
        config(['onelink.allowed_ips' => ['127.0.0.0/8']]);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertJson(['response_code' => '00']);
    }

    public function test_disabled_service_returns_08(): void
    {
        config(['onelink.enabled' => false]);
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000)->assertOk()
            ->assertJson(['response_code' => '08', 'response_description' => 'Service temporarily unavailable']);
    }

    public function test_bank_mnemonic_enforced_when_configured(): void
    {
        config(['onelink.bank_mnemonic' => 'HBL']);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertJson(['response_code' => '04']);
    }

    // ---------- always HTTP 200 ----------

    public function test_rate_limit_returns_http_200_with_code_08(): void
    {
        config(['onelink.rate_limit' => 1]);
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertOk()->assertJson(['response_code' => '00']);
        $this->inquiry($c->fresh()->challan_no)->assertOk()->assertJson(['response_code' => '08']);
    }

    public function test_unknown_endpoint_and_wrong_method_return_http_200(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->postJson('/api/onelink/unknown', [])
            ->assertOk()->assertJson(['response_code' => '07']);

        $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->getJson('/api/onelink/inquiry')
            ->assertOk()->assertJson(['response_code' => '07']);
    }

    // ---------- inquiry ----------

    public function test_inquiry_unpaid_on_due_date(): void
    {
        $c = $this->makeChallan();
        $response = $this->inquiry($c->fresh()->challan_no)
            ->assertOk()
            ->assertExactJson([
                'response_code' => '00',
                'response_description' => 'Voucher is valid',
                'institute_code' => '123456',
                'institute_name' => 'DPS Test School',
                'one_link_channel' => 1,
                'consumer_detail' => 'Ali S/O Ahmed Khan',
                'bill_status' => 'Unpaid',
                'due_date' => '20260115',
                'amount_within_dueDate' => 25000,
                'amount_after_dueDate' => 25500,
                'amount_receivable' => 25000,
                'late_fee' => 500,
                'billing_month' => '202601',
                'tran_auth_Id' => '',
            ]);

        // Amounts are JSON integers, not strings.
        $this->assertIsInt($response->json('amount_receivable'));
        $this->assertStringContainsString('"amount_receivable":25000', $response->getContent());
    }

    public function test_inquiry_with_prefix_still_resolves(): void
    {
        $c = $this->makeChallan();
        $this->inquiry(self::PREFIX . $c->fresh()->challan_no)->assertJson(['response_code' => '00']);
    }

    public function test_inquiry_echoes_branch_counter_channel(): void
    {
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no, [], 0)->assertJson(['response_code' => '00', 'one_link_channel' => 0]);
    }

    public function test_invalid_channel_rejected(): void
    {
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no, [], 2)->assertJson(['response_code' => '07', 'response_description' => 'Invalid request: channel']);
    }

    public function test_non_numeric_consumer_number_rejected(): void
    {
        $this->inquiry('ABC123')->assertJson(['response_code' => '07', 'response_description' => 'Invalid request: consumer_number']);
    }

    public function test_amounts_are_rounded_to_whole_rupees(): void
    {
        $c = $this->makeChallan(['total_amount' => 25000.50, 'amount_after_due_date' => 25500.50]);
        $this->inquiry($c->fresh()->challan_no)->assertJson(['amount_receivable' => 25001, 'late_fee' => 500]);
        $this->pay($c->fresh()->challan_no, 25001)->assertJson(['response_code' => '00']);
    }

    public function test_inquiry_after_due_date_includes_late_fee_and_daughter(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 16, 0, 30, 0, 'Asia/Karachi'));
        $c = $this->makeChallan([], 'Female');
        $this->inquiry($c->fresh()->challan_no)
            ->assertJson(['amount_receivable' => 25500, 'consumer_detail' => 'Ali D/O Ahmed Khan']);
    }

    public function test_due_date_uses_pakistan_time_not_utc(): void
    {
        // 2026-01-16 01:00 PKT = 2026-01-15 20:00 UTC -> already late in Pakistan
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 20, 0, 0, 'UTC'));
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no)->assertJson(['amount_receivable' => 25500]);
    }

    public function test_inquiry_unknown_consumer(): void
    {
        $this->inquiry('99999999')->assertExactJson(['response_code' => '01', 'response_description' => 'Voucher is invalid']);
    }

    public function test_inquiry_zero_amount_is_invalid_voucher(): void
    {
        $c = $this->makeChallan(['total_amount' => 0, 'amount_after_due_date' => 0]);
        $this->inquiry($c->fresh()->challan_no)->assertJson(['response_code' => '01']);
    }

    public function test_inquiry_missing_fields(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->postJson('/api/onelink/inquiry', ['bank_mnemonic' => 'BOP'])
            ->assertJson(['response_code' => '07', 'response_description' => 'Invalid request: consumer_number, channel']);
    }

    public function test_inquiry_finds_old_short_challan_numbers_even_zero_padded(): void
    {
        $this->makeChallan(['challan_no' => '4521']);
        $this->inquiry('0000' . '4521')->assertJson(['response_code' => '00', 'bill_status' => 'Unpaid']);
        $this->inquiry(self::PREFIX . '4521')->assertJson(['response_code' => '00']);
    }

    public function test_expired_challan(): void
    {
        $c = $this->makeChallan(['status' => FeeChallan::STATUS_EXPIRED]);
        $this->inquiry($c->fresh()->challan_no)->assertExactJson(['response_code' => '03', 'response_description' => 'Voucher expired']);
        $this->pay($c->fresh()->challan_no, 25000)->assertJson(['response_code' => '03']);
        $this->assertSame('expired', $c->fresh()->status);
        $this->assertSame(0, ChallanPayment::count());
    }

    public function test_review_status_is_still_payable(): void
    {
        $c = $this->makeChallan(['status' => 'review']);
        ChallanPayment::create(['fee_challan_id' => $c->id, 'bank_name' => 'HBL', 'voucher_image' => 'upload/x.jpg']);

        $this->inquiry($c->fresh()->challan_no)->assertJson(['bill_status' => 'Unpaid']);
        $this->pay($c->fresh()->challan_no, 25000)->assertJson(['response_code' => '00']);

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
        $this->pay($c->fresh()->challan_no, 25000)
            ->assertOk()
            ->assertExactJson([
                'response_code' => '00',
                'response_description' => 'Payment posted successfully',
                'institute_code' => '123456',
                'institute_name' => 'DPS Test School',
                'one_link_channel' => 1,
                'Identification_parameter' => 'Ali S/O Ahmed Khan',
            ]);

        $c->refresh()->load('payment');
        $this->assertSame('approved', $c->status);
        $this->assertSame('onelink', $c->payment->source);
        $this->assertSame('AUTH1', $c->payment->tran_auth_id);
        $this->assertSame(1, (int) $c->payment->channel);
        $this->assertSame('2026-01-15', $c->payment->paid_date->toDateString());
        $this->assertNull($c->payment->voucher_image);

        // Inquiry on a paid voucher: code 02 with the payment details.
        $this->inquiry($c->challan_no)->assertJson([
            'response_code' => '02', 'response_description' => 'Voucher already paid',
            'bill_status' => 'Paid', 'date_paid' => '20260115', 'tran_auth_Id' => 'AUTH1', 'amount_receivable' => 25000,
        ]);
    }

    public function test_branch_counter_payment_records_channel_0(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH1', '20260115', '143000', 0)
            ->assertJson(['response_code' => '00', 'one_link_channel' => 0]);
        $this->assertSame(0, (int) $c->fresh()->payment->channel);
    }

    public function test_payment_amount_mismatch(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25500)
            ->assertExactJson(['response_code' => '05', 'response_description' => 'Amount does not match the payable amount']);
        $this->assertSame('pending', $c->fresh()->status);
    }

    public function test_payment_uses_bank_transaction_date_for_late_fee(): void
    {
        // Reaches us after midnight, but bank transaction happened on the due date.
        Carbon::setTestNow(Carbon::create(2026, 1, 16, 0, 5, 0, 'Asia/Karachi'));
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH9', '20260115', '235930')->assertJson(['response_code' => '00']);
    }

    public function test_late_payment_requires_late_amount(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'A1', '20260116')->assertJson(['response_code' => '05']);
        $this->pay($c->fresh()->challan_no, 25500, 'A2', '20260116')->assertJson(['response_code' => '00']);
    }

    public function test_already_paid_returns_02(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_code' => '00']);
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH2')->assertExactJson(['response_code' => '02', 'response_description' => 'Voucher already paid']);
    }

    public function test_retry_with_same_tran_auth_id_is_idempotent(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_code' => '00']);
        $this->pay($c->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_code' => '00']);
        $this->assertSame(1, ChallanPayment::where('tran_auth_id', 'AUTH1')->count());
    }

    public function test_same_tran_auth_id_on_another_challan_rejected(): void
    {
        $a = $this->makeChallan();
        $b = $this->makeChallan(['month' => '2026-02']);
        $this->pay($a->fresh()->challan_no, 25000, 'AUTH1')->assertJson(['response_code' => '00']);
        $this->pay($b->fresh()->challan_no, 25000, 'AUTH1')->assertExactJson(['response_code' => '06', 'response_description' => 'Duplicate transaction ID']);
        $this->assertSame('pending', $b->fresh()->status);
    }

    public function test_payment_missing_fields(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . self::TOKEN])
            ->postJson('/api/onelink/payment', ['consumer_number' => '1'])
            ->assertJson(['response_code' => '07']);
    }

    public function test_payment_accepts_string_and_padded_amounts(): void
    {
        $a = $this->makeChallan();
        $b = $this->makeChallan(['month' => '2026-02']);
        $this->pay($a->fresh()->challan_no, '25000', 'S1')->assertJson(['response_code' => '00']);
        $this->pay($b->fresh()->challan_no, '+0000002500000', 'P1')->assertJson(['response_code' => '00']);
    }

    public function test_amount_helpers(): void
    {
        $this->assertSame(27500.0, OneLinkAmount::parse('27500'));
        $this->assertSame(27500.5, OneLinkAmount::parse('27500.50'));
        $this->assertSame(27500.0, OneLinkAmount::parse('+0000002750000'));
        $this->assertSame(27500.0, OneLinkAmount::parse(27500));
        $this->assertNull(OneLinkAmount::parse('abc'));
        $this->assertSame(27501, OneLinkAmount::format(27500.5));
        $this->assertSame(27500, OneLinkAmount::format(27500.0));
    }

    public function test_every_request_is_logged(): void
    {
        $c = $this->makeChallan();
        $this->inquiry($c->fresh()->challan_no);
        $this->pay($c->fresh()->challan_no, 25000);
        $this->withHeaders(['Authorization' => 'Bearer bad'])->postJson('/api/onelink/inquiry', []);

        $this->assertSame(['inquiry', 'payment', 'rejected_inquiry'], OneLinkLog::orderBy('id')->pluck('type')->all());
        $this->assertSame($c->id, OneLinkLog::where('type', 'payment')->first()->fee_challan_id);
        $this->assertSame('04', OneLinkLog::where('type', 'rejected_inquiry')->first()->response_code);
    }

    public function test_admin_cannot_revert_or_delete_onelink_paid_challan(): void
    {
        $c = $this->makeChallan();
        $this->pay($c->fresh()->challan_no, 25000)->assertJson(['response_code' => '00']);

        $controller = app(\App\Http\Controllers\Backend\Admin\ChallanController::class);
        $controller->pendingChallan($c->id);
        $controller->destroyChallan($c->id);

        $this->assertSame('approved', $c->fresh()->status);
        $this->assertNull($c->fresh()->deleted_at);
    }
}
