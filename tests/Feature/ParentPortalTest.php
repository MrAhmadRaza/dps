<?php

namespace Tests\Feature;

use App\Http\Middleware\Parent\UseParentSessionCookie;
use App\Models\AcademicSession;
use App\Models\ChallanPayment;
use App\Models\FeeChallan;
use App\Models\GeneralSetting;
use App\Models\Parant;
use App\Models\SessionItem;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use RefreshDatabase;

    private Parant $parent;
    private Student $child;
    private FeeChallan $challan;
    private FeeChallan $otherChallan;
    private Student $otherChild;

    /** Proof files written during a test, removed in tearDown. */
    private array $writtenProofs = [];

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 1, 10, 10, 0, 0, 'Asia/Karachi'));
        config(['onelink.enabled' => true, 'onelink.timezone' => 'Asia/Karachi']);

        GeneralSetting::create(['name' => 'DPS', 'account_no' => '1', 'one_bill_prefix' => '10045', 'late_fee_fine' => 500]);

        $session = AcademicSession::create(['session_name' => '2025-26', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
        $item = SessionItem::create(['academic_session_id' => $session->id, 'class' => '5', 'section' => 'A']);

        $this->parent = $this->makeParent('35202-1234567-1', 'secret1');
        $this->child = $this->makeStudent($this->parent, 'Ali Khan', $session->id, $item->id);
        $this->challan = $this->makeChallan($this->child);

        $other = $this->makeParent('35202-7654321-9', 'other12');
        $this->otherChild = $this->makeStudent($other, 'Sara Ahmed', $session->id, $item->id);
        $this->otherChallan = $this->makeChallan($this->otherChild);
    }

    protected function tearDown(): void
    {
        foreach ($this->writtenProofs as $path) {
            if (is_file(public_path($path))) {
                @unlink(public_path($path));
            }
        }
        foreach (ChallanPayment::withTrashed()->pluck('voucher_image')->filter() as $path) {
            if (str_starts_with($path, 'upload/challans/') && is_file(public_path($path))) {
                @unlink(public_path($path));
            }
        }
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeParent(string $nic, string $password): Parant
    {
        return Parant::create([
            'father_name' => 'Ahmed Khan', 'father_nic' => $nic, 'password' => Hash::make($password),
            'mother_name' => 'Ayesha', 'occupation' => 'Teacher', 'address' => 'Dunyapur',
            'contact_no' => (string) random_int(10000000000, 99999999999),
        ]);
    }

    private function makeStudent(Parant $parent, string $name, int $sessionId, int $itemId): Student
    {
        return Student::create([
            'parent_id' => $parent->id, 'name' => $name, 'date_of_birth' => '2015-01-01', 'gender' => 'Male',
            'status' => 'active', 'academic_session_id' => $sessionId, 'session_item_id' => $itemId,
        ]);
    }

    private function makeChallan(Student $student, array $overrides = []): FeeChallan
    {
        return FeeChallan::create(array_merge([
            'student_id' => $student->id, 'month' => '2026-01', 'status' => 'pending',
            'issue_date' => '2026-01-01', 'due_date' => '2026-01-15',
            'total_amount' => 25000, 'amount_after_due_date' => 25500,
        ], $overrides));
    }

    private function asParent()
    {
        return $this->actingAs($this->parent, 'parents');
    }

    private function proof(): UploadedFile
    {
        return UploadedFile::fake()->image('receipt.jpg', 800, 600);
    }

    // ---------------------------------------------------------------- access

    public function test_guest_is_sent_to_sign_in(): void
    {
        foreach (['/parent/dashboard', '/parent/challan/fee-challan', '/parent/student', '/parent/account'] as $url) {
            $this->get($url)->assertRedirect(route('parent.signIn'));
        }
    }

    public function test_all_pages_render_for_a_parent(): void
    {
        $this->asParent();

        $this->get(route('parent.index'))->assertOk()->assertSee('Ali Khan')->assertSee('25,000')->assertSee('10045' . $this->challan->challan_no);
        $this->get(route('parent.fee-challan'))->assertOk()->assertSee('Jan 2026')->assertDontSee('Sara Ahmed');
        $this->get(route('parent.view.challan', $this->challan->id))->assertOk()->assertSee('Fee breakdown');
        $this->get(route('parent.challan.upload.form', $this->challan->id))->assertOk()->assertSee('Upload payment proof');
        $this->get(route('parent.print.challan', $this->challan->id))->assertOk();
        $this->get(route('parent.student.index'))->assertOk()->assertSee('Ali Khan');
        $this->get(route('parent.view.student', $this->child->id))->assertOk()->assertSee('35202-1234567-1');
        $this->get(route('parent.account'))->assertOk()->assertSee('Change password');
    }

    public function test_parent_cannot_open_another_familys_student(): void
    {
        $this->asParent()->get(route('parent.view.student', $this->otherChild->id))->assertNotFound();
    }

    public function test_parent_cannot_open_another_familys_challan(): void
    {
        $this->asParent();
        $this->get(route('parent.view.challan', $this->otherChallan->id))->assertNotFound();
        $this->get(route('parent.print.challan', $this->otherChallan->id))->assertNotFound();
        $this->get(route('parent.challan.upload.form', $this->otherChallan->id))->assertNotFound();
    }

    public function test_inactive_child_is_hidden(): void
    {
        $this->child->update(['status' => 'inactive']);
        $this->asParent();
        $this->get(route('parent.view.student', $this->child->id))->assertNotFound();
        $this->get(route('parent.view.challan', $this->challan->id))->assertNotFound();
    }

    public function test_fee_filter_ignores_another_familys_student_id(): void
    {
        $this->asParent()->get(route('parent.fee-challan', ['student' => $this->otherChild->id]))
            ->assertOk()->assertDontSee('Sara Ahmed')->assertSee('Ali Khan');
    }

    // ---------------------------------------------------------------- amounts

    public function test_amounts_match_onelink_before_and_after_due_date(): void
    {
        $this->asParent()->get(route('parent.index'))->assertSee('Rs 25,500')->assertSee('25,000');

        Carbon::setTestNow(Carbon::create(2026, 1, 16, 10, 0, 0, 'Asia/Karachi'));
        $this->get(route('parent.index'))->assertSee('25,500')->assertSee('Overdue')->assertSee('late fee');
    }

    // ---------------------------------------------------------------- upload

    public function test_parent_can_upload_proof_for_own_unpaid_challan(): void
    {
        $this->asParent()->post(route('parent.challan.upload'), [
            'fee_challan_id' => $this->challan->id, 'bank_name' => 'HBL', 'paid_date' => '2026-01-09',
            'voucher_image' => $this->proof(),
        ])->assertRedirect(route('parent.view.challan', $this->challan->id));

        $this->assertSame('review', $this->challan->fresh()->status);
        $payment = ChallanPayment::where('fee_challan_id', $this->challan->id)->firstOrFail();
        $this->assertSame(ChallanPayment::SOURCE_MANUAL, $payment->source);
        $this->assertMatchesRegularExpression('#^upload/challans/[A-Za-z0-9]{40}\.jpg$#', $payment->voucher_image);
        $this->assertFileExists(public_path($payment->voucher_image));
    }

    public function test_parent_cannot_upload_against_another_familys_challan(): void
    {
        $this->asParent()->post(route('parent.challan.upload'), [
            'fee_challan_id' => $this->otherChallan->id, 'bank_name' => 'HBL', 'paid_date' => '2026-01-09',
            'voucher_image' => $this->proof(),
        ])->assertNotFound();

        $this->assertSame('pending', $this->otherChallan->fresh()->status);
        $this->assertDatabaseCount('challan_payments', 0);
    }

    public function test_upload_refused_for_paid_or_review_challan(): void
    {
        foreach (['approved', 'review', 'expired'] as $status) {
            $this->challan->update(['status' => $status]);
            $this->asParent()->post(route('parent.challan.upload'), [
                'fee_challan_id' => $this->challan->id, 'bank_name' => 'HBL', 'paid_date' => '2026-01-09',
                'voucher_image' => $this->proof(),
            ])->assertSessionHas('error');
            $this->assertSame($status, $this->challan->fresh()->status);
        }
        $this->assertDatabaseCount('challan_payments', 0);
    }

    public function test_upload_reuses_soft_deleted_payment_row(): void
    {
        $old = ChallanPayment::create(['fee_challan_id' => $this->challan->id, 'bank_name' => 'Old', 'voucher_image' => null]);
        $old->delete();

        $this->asParent()->post(route('parent.challan.upload'), [
            'fee_challan_id' => $this->challan->id, 'bank_name' => 'Meezan Bank', 'paid_date' => '2026-01-09',
            'voucher_image' => $this->proof(),
        ])->assertRedirect();

        $this->assertSame(1, ChallanPayment::withTrashed()->where('fee_challan_id', $this->challan->id)->count());
        $this->assertSame('Meezan Bank', ChallanPayment::where('fee_challan_id', $this->challan->id)->value('bank_name'));
    }

    public function test_upload_validation_errors_are_shown(): void
    {
        $this->asParent()->from(route('parent.challan.upload.form', $this->challan->id))
            ->post(route('parent.challan.upload'), ['fee_challan_id' => $this->challan->id, 'paid_date' => '2030-01-01'])
            ->assertSessionHasErrors(['bank_name', 'paid_date', 'voucher_image']);
    }

    // ---------------------------------------------------------------- sign-in

    public function test_sign_in_and_out(): void
    {
        $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'secret1'])
            ->assertRedirect(route('parent.index'));
        $this->assertAuthenticatedAs($this->parent, 'parents');

        $this->post(route('parent.logout'))->assertRedirect(route('parent.signIn'));
        $this->assertGuest('parents');
    }

    public function test_keep_me_signed_in_sets_remember_cookie(): void
    {
        $response = $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'secret1', 'remember' => '1'])
            ->assertRedirect(route('parent.index'));

        $this->assertNotNull($this->parent->fresh()->remember_token);
        $this->assertTrue(collect($response->headers->getCookies())->contains(fn ($c) => str_starts_with($c->getName(), 'remember_parents_')));
    }

    public function test_keep_me_signed_in_works_before_migration_is_run(): void
    {
        \Illuminate\Support\Facades\Schema::table('parents', fn ($t) => $t->dropColumn('remember_token'));

        $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'secret1', 'remember' => '1'])
            ->assertRedirect(route('parent.index'));
        $this->assertAuthenticatedAs($this->parent, 'parents');

        // Signing out must not try to write the missing column either
        $this->post(route('parent.logout'))->assertRedirect(route('parent.signIn'));
        $this->assertGuest('parents');
    }

    public function test_sign_in_with_cnic_stored_as_plain_digits(): void
    {
        $this->parent->update(['father_nic' => '3520212345671']);

        // Typed with dashes (the sign-in form formats it)
        $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'secret1'])
            ->assertRedirect(route('parent.index'));
        $this->assertAuthenticatedAs($this->parent->fresh(), 'parents');
    }

    public function test_sign_in_with_plain_digits_typed(): void
    {
        $this->post(route('parent.signIn.submit'), ['father_nic' => '3520212345671', 'password' => 'secret1'])
            ->assertRedirect(route('parent.index'));
        $this->assertAuthenticatedAs($this->parent, 'parents');
    }

    public function test_plain_text_stored_password_is_refused_without_crashing(): void
    {
        \DB::table('parents')->where('id', $this->parent->id)->update(['password' => 'secret1']);

        $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'secret1'])
            ->assertRedirect()->assertSessionHas('error', 'The CNIC or password is incorrect.');
        $this->assertGuest('parents');
    }

    public function test_wrong_password_shows_error(): void
    {
        $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'wrong-pass'])
            ->assertSessionHas('error', 'The CNIC or password is incorrect.');
        $this->assertGuest('parents');
    }

    public function test_sign_in_is_throttled(): void
    {
        RateLimiter::clear('parent-login:3520212345671|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'wrong-pass']);
        }

        // Even the right password is refused while locked out
        $this->post(route('parent.signIn.submit'), ['father_nic' => '35202-1234567-1', 'password' => 'secret1'])
            ->assertSessionHas('error', fn ($m) => str_starts_with($m, 'Too many sign-in attempts'));
        $this->assertGuest('parents');
    }

    public function test_signed_in_parent_skips_sign_in_page(): void
    {
        $this->asParent()->get(route('parent.signIn'))->assertRedirect(route('parent.index'));
    }

    public function test_parent_pages_use_their_own_session_cookie(): void
    {
        $response = $this->get(route('parent.signIn'));
        $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === UseParentSessionCookie::COOKIE);

        $this->assertNotNull($cookie, 'parent session cookie not set');
        $this->assertSame('/parent', $cookie->getPath());

        $admin = $this->get('/admin/sign-in');
        $this->assertNull(collect($admin->headers->getCookies())->first(fn ($c) => $c->getName() === UseParentSessionCookie::COOKIE));
    }

    // ---------------------------------------------------------------- account

    public function test_parent_can_change_password(): void
    {
        $this->asParent()->post(route('parent.account.password'), [
            'current_password' => 'secret1', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertRedirect(route('parent.account'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password-1', $this->parent->fresh()->password));
    }

    public function test_change_password_requires_current_password(): void
    {
        $this->asParent()->post(route('parent.account.password'), [
            'current_password' => 'nope', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('secret1', $this->parent->fresh()->password));
    }
}
