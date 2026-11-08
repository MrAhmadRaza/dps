<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Auth\AdminSignInController;
use App\Http\Middleware\Admin\UseAdminSessionCookie;
use App\Http\Middleware\Parent\UseParentSessionCookie;
use App\Models\AcademicSession;
use App\Models\Category;
use App\Models\ChallanPayment;
use App\Models\FeeChallan;
use App\Models\FeeChallanItem;
use App\Models\GeneralSetting;
use App\Models\HeadCategory;
use App\Models\Parant;
use App\Models\Role;
use App\Models\SessionItem;
use App\Models\Student;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherItem;
use App\Services\AdminPortal\ChallanStatus;
use App\Services\AdminPortal\DashboardStats;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Student $student;
    private Parant $parent;
    private SessionItem $item;
    private AcademicSession $session;
    private Voucher $voucher;
    private Category $category;
    private HeadCategory $head;

    protected function setUp(): void
    {
        parent::setUp();

        // "today" in Pakistan: 2026-11-15
        Carbon::setTestNow(Carbon::create(2026, 11, 15, 10, 0, 0, 'Asia/Karachi'));
        config(['onelink.timezone' => 'Asia/Karachi']);

        $superRole = Role::create(['role_name' => 'superadmin']);
        Role::create(['role_name' => 'admin']);
        Role::create(['role_name' => 'teacher']);

        $this->admin = User::create([
            'name' => 'Office Admin', 'email' => 'office@dps.test', 'password' => 'secret-pass', 'role_id' => $superRole->id,
        ]);

        GeneralSetting::create(['name' => 'DPS', 'account_no' => '123', 'one_bill_prefix' => '10045', 'late_fee_fine' => 500]);

        $this->session = AcademicSession::create(['session_name' => '2026-27', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'status' => 'active']);
        $this->item = SessionItem::create(['academic_session_id' => $this->session->id, 'class' => '5', 'section' => 'A']);
        $this->voucher = Voucher::create(['academic_session_id' => $this->session->id, 'session_item_id' => $this->item->id, 'due_date' => '2026-11-10', 'total_amount' => 5000]);
        VoucherItem::create(['voucher_id' => $this->voucher->id, 'fee_name' => 'Tuition fee', 'amount' => 5000]);

        $this->parent = Parant::create([
            'father_name' => 'Ahmed Khan', 'father_nic' => '3520212345671', 'password' => Hash::make('1234'),
            'occupation' => 'Teacher', 'address' => 'Dunyapur', 'contact_no' => '03001234567',
        ]);
        $this->student = Student::create([
            'parent_id' => $this->parent->id, 'name' => 'Ali Khan', 'date_of_birth' => '2015-01-01', 'gender' => 'Male',
            'academic_session_id' => $this->session->id, 'session_item_id' => $this->item->id, 'status' => 'active',
            'admission_date' => '2026-04-05',
        ]);

        $this->category = Category::create(['category_name' => 'Salaries', 'type' => 'expense']);
        $this->head = HeadCategory::create(['category_id' => $this->category->id, 'date' => '2026-11-02', 'bill_no' => 'B1', 'head_name' => 'November salaries', 'amount' => 90000]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        RateLimiter::clear('admin-sign-in:office@dps.test|127.0.0.1');
        parent::tearDown();
    }

    private function challan(string $status, string $month = '2026-11', string $due = '2026-11-20', float $amount = 5000): FeeChallan
    {
        // One challan per student per month (unique index): use a brother when the month is taken.
        $student = $this->student;
        if (FeeChallan::withTrashed()->where('student_id', $student->id)->where('month', $month)->exists()) {
            $student = Student::create([
                'parent_id' => $this->parent->id, 'name' => 'Brother ' . uniqid(), 'date_of_birth' => '2016-01-01', 'gender' => 'Male',
                'academic_session_id' => $this->session->id, 'session_item_id' => $this->item->id, 'status' => 'active',
            ]);
        }
        $c = FeeChallan::create([
            'student_id' => $student->id, 'month' => $month, 'status' => $status,
            'issue_date' => $month . '-01', 'due_date' => $due, 'total_amount' => $amount, 'amount_after_due_date' => $amount + 500,
        ]);
        FeeChallanItem::create(['fee_challan_id' => $c->id, 'fee_item_id' => 1, 'fee_item_name' => 'Tuition fee', 'amount' => $amount]);

        return $c;
    }

    private function withProof(FeeChallan $c, string $source = 'manual'): FeeChallan
    {
        ChallanPayment::create([
            'fee_challan_id' => $c->id, 'bank_name' => $source === 'onelink' ? '1LINK / HBL' : 'HBL', 'paid_date' => '2026-11-05',
            'transaction_id' => 'TX1', 'voucher_image' => $source === 'onelink' ? '' : 'upload/challans/missing.jpg', 'source' => $source,
            'amount' => $source === 'onelink' ? $c->total_amount : null,
        ]);

        return $c;
    }

    private function asAdmin(): static
    {
        return $this->actingAs($this->admin, 'web');
    }

    private function ajax(string $url)
    {
        return $this->asAdmin()->getJson($url, ['X-Requested-With' => 'XMLHttpRequest']);
    }

    // ------------------------------------------------------------------ access

    public function test_guests_are_sent_to_staff_sign_in(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('admin.signIn'));
        $this->get('/admin/parent/challans')->assertRedirect(route('admin.signIn'));
        $this->get('/admin')->assertRedirect('/admin/dashboard');
    }

    public function test_sign_in_page_renders(): void
    {
        $this->get(route('admin.signIn'))->assertOk()->assertSee('Staff sign in')->assertSee('admin_assets/css/admin.css', false);
    }

    public function test_staff_can_sign_in_and_out(): void
    {
        $this->post(route('admin.signIn.submit'), ['email' => 'office@dps.test', 'password' => 'secret-pass'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'web');

        $this->post(route('admin.logout'))->assertRedirect(route('admin.signIn'));
        $this->assertGuest('web');
    }

    public function test_wrong_password_shows_error_and_throttles(): void
    {
        for ($i = 0; $i < AdminSignInController::MAX_ATTEMPTS; $i++) {
            $this->from(route('admin.signIn'))
                ->post(route('admin.signIn.submit'), ['email' => 'office@dps.test', 'password' => 'nope'])
                ->assertRedirect(route('admin.signIn'))
                ->assertSessionHasErrors(['email' => 'That email and password do not match a staff account.']);
        }

        // Even the right password is refused while locked out
        $this->from(route('admin.signIn'))
            ->post(route('admin.signIn.submit'), ['email' => 'office@dps.test', 'password' => 'secret-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertStringContainsString('Too many sign-in attempts', session('errors')->first('email'));
    }

    public function test_user_without_staff_role_cannot_sign_in(): void
    {
        User::create(['name' => 'T', 'email' => 't@dps.test', 'password' => 'secret-pass', 'role_id' => Role::where('role_name', 'teacher')->value('id')]);

        $this->from(route('admin.signIn'))
            ->post(route('admin.signIn.submit'), ['email' => 't@dps.test', 'password' => 'secret-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }

    public function test_signed_in_staff_skip_sign_in_page(): void
    {
        $this->asAdmin()->get(route('admin.signIn'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_pages_use_their_own_session_cookie(): void
    {
        $admin = $this->get('/admin/sign-in');
        $cookie = collect($admin->headers->getCookies())->first(fn ($c) => $c->getName() === UseAdminSessionCookie::COOKIE);
        $this->assertNotNull($cookie, 'admin session cookie not set');
        $this->assertSame('/admin', $cookie->getPath());

        // Public site keeps the default cookie, parent portal keeps its own
        $public = $this->get('/');
        $this->assertNull(collect($public->headers->getCookies())->first(fn ($c) => $c->getName() === UseAdminSessionCookie::COOKIE));

        $parent = $this->get('/parent/sign-in');
        $names = collect($parent->headers->getCookies())->map->getName();
        $this->assertContains(UseParentSessionCookie::COOKIE, $names);
        $this->assertNotContains(UseAdminSessionCookie::COOKIE, $names);

        // The setting does not leak into the next request
        $this->assertNotSame(UseAdminSessionCookie::COOKIE, config('session.cookie'));
    }

    // ------------------------------------------------------------------ pages

    public function test_every_admin_page_renders(): void
    {
        $c = $this->withProof($this->challan('review'));

        $pages = [
            route('admin.dashboard'),
            route('admin.admission.index'),
            route('admin.add.admission'),
            route('admin.view.admission', $this->student->id),
            route('admin.edit.admission', $this->student->id),
            route('admin.print.admission', $this->student->id),
            route('admin.voucher.admission', $this->student->id),
            route('admin.challan.print', $c->id),
            route('admin.academic-session.index'),
            route('admin.academic-session.add'),
            route('admin.academic-session.view.index', $this->session->id),
            route('admin.academic-session.edit', $this->session->id),
            route('admin.academic-session-item.edit', $this->item->id),
            route('admin.voucher.index'),
            route('admin.voucher.add'),
            route('admin.voucher.view', $this->voucher->id),
            route('admin.voucher.edit', $this->voucher->id),
            route('admin.voucher.print', $this->voucher->id),
            route('admin.challan.index'),
            route('admin.challan.index', ['status' => 'review']),
            route('admin.challan.view', $c->id),
            route('admin.parent.show'),
            route('admin.parent.password', $this->parent->id),
            route('admin.categories.index'),
            route('admin.categories.add'),
            route('admin.categories.edit', $this->category->id),
            route('admin.categories.headcategory', $this->category->id),
            route('admin.categories.edithead', $this->head->id),
            route('admin.categories.print'),
            route('admin.categories.head.print', $this->category->id),
            route('admin.reports.print'),
            route('admin.reports.cashbook', ['month' => 11, 'year' => 2026]),
            route('admin.general-settings.index'),
        ];

        foreach ($pages as $url) {
            $this->asAdmin()->get($url)->assertOk();
        }
    }

    public function test_layout_loads_only_admin_assets(): void
    {
        $html = $this->asAdmin()->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('admin_assets/css/admin.css', $html);
        $this->assertStringContainsString('admin_assets/js/admin.js', $html);
        $this->assertStringNotContainsString('backend_assets', $html);
        $this->assertStringNotContainsString('parent_assets', $html);
        $this->assertStringNotContainsString('frontend_assets', $html);
    }

    public function test_datatables_endpoints_return_json(): void
    {
        $this->withProof($this->challan('review'));

        foreach ([
            route('admin.admission.index'),
            route('admin.academic-session.index'),
            route('admin.voucher.index'),
            route('admin.challan.index'),
            route('admin.parent.show'),
            route('admin.categories.index'),
            route('admin.categories.showHeadCategories', $this->category->id),
            route('admin.admission.challans', $this->student->id),
        ] as $url) {
            $this->ajax($url . (str_contains($url, '?') ? '&' : '?') . 'draw=1&start=0&length=10')
                ->assertOk()->assertJsonStructure(['data', 'recordsTotal']);
        }
    }

    // ------------------------------------------------------------------ dashboard

    public function test_dashboard_shows_real_numbers_and_review_queue(): void
    {
        $this->challan('approved', '2026-11', '2026-11-20', 4000);
        $this->withProof($this->challan('review', '2026-11', '2026-11-20', 3000));
        $this->challan('pending', '2026-10', '2026-10-10', 2000); // overdue

        $stats = app(DashboardStats::class)->all();
        $this->assertSame(1, $stats['review_count']);
        $this->assertSame(1, $stats['overdue_count']);
        $this->assertSame(7000.0, $stats['this_month']['billed']);
        $this->assertSame(4000.0, $stats['this_month']['collected']);
        $this->assertSame(57, $stats['this_month']['rate']);
        $this->assertSame(2, $stats['students_active']);
        $this->assertCount(6, $stats['trend']);
        $this->assertSame('Nov', end($stats['trend'])['label']);

        $this->asAdmin()->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Review queue')->assertSee('Ali Khan')->assertSee('Proofs to review');
    }

    // ------------------------------------------------------------------ challans

    public function test_challan_status_labels(): void
    {
        $s = app(ChallanStatus::class);
        $this->assertSame('unpaid', $s->for($this->challan('pending', '2026-11', '2026-11-20'))['key']);
        $this->assertSame('overdue', $s->for($this->challan('pending', '2026-10', '2026-10-10'))['key']);
        $this->assertSame('review', $s->for($this->challan('review'))['key']);
        $this->assertSame('expired', $s->for($this->challan('expired'))['key']);

        $paid = $this->withProof($this->challan('approved'), 'onelink')->fresh('payment');
        $this->assertSame('paid', $s->for($paid)['key']);
        $this->assertTrue($s->for($paid)['onelink']);
    }

    public function test_challan_list_filters_by_status(): void
    {
        $this->challan('pending', '2026-11', '2026-11-20');
        $this->challan('pending', '2026-10', '2026-10-10');
        $this->withProof($this->challan('review'));
        $this->challan('approved', '2026-09');
        $this->challan('expired', '2026-08');

        $expect = ['all' => 5, 'review' => 1, 'unpaid' => 1, 'overdue' => 1, 'paid' => 1, 'expired' => 1];
        $this->assertSame($expect, app(ChallanStatus::class)->counts());

        foreach ($expect as $key => $n) {
            $this->ajax(route('admin.challan.index', ['status' => $key, 'draw' => 1, 'start' => 0, 'length' => 25]))
                ->assertOk()->assertJsonCount($n, 'data');
        }

        $this->asAdmin()->get(route('admin.challan.index', ['status' => 'overdue']))->assertOk()->assertSee('Needs review');
    }

    public function test_approve_and_pending_require_post(): void
    {
        $c = $this->withProof($this->challan('review'));

        $this->asAdmin()->get('/admin/parent/challans/approved-chalan/' . $c->id)->assertStatus(405);
        $this->assertSame('review', $c->fresh()->status);

        $this->asAdmin()->from(route('admin.challan.index'))->post(route('admin.challan.approved', $c->id))
            ->assertRedirect(route('admin.challan.index'))->assertSessionHas('success');
        $this->assertSame('approved', $c->fresh()->status);

        $this->asAdmin()->get('/admin/parent/challans/pending-chalan/' . $c->id)->assertStatus(405);
        $this->asAdmin()->post(route('admin.challan.pending', $c->id));
        $this->assertSame('pending', $c->fresh()->status);
    }

    public function test_onelink_paid_challan_cannot_be_reverted_or_deleted(): void
    {
        $c = $this->withProof($this->challan('approved'), 'onelink');

        $this->asAdmin()->post(route('admin.challan.pending', $c->id))->assertSessionHas('error');
        $this->asAdmin()->delete(route('admin.challan.destroy', $c->id))->assertSessionHas('error');

        $this->assertSame('approved', $c->fresh()->status);
        $this->assertNull($c->fresh()->deleted_at);
        $this->asAdmin()->get(route('admin.challan.view', $c->id))->assertOk()->assertSee('1LINK / 1Bill')->assertDontSee('Delete challan');
    }

    public function test_deleting_from_the_challan_page_returns_to_the_list(): void
    {
        $c = $this->challan('pending');

        $this->asAdmin()->from(route('admin.challan.view', $c->id))->delete(route('admin.challan.destroy', $c->id))
            ->assertRedirect(route('admin.challan.index'));
        $this->assertSoftDeleted($c);

        $this->asAdmin()->get(route('admin.challan.view', $c->id))->assertRedirect(route('admin.challan.index'));
    }

    public function test_challan_detail_shows_amounts_and_1bill_id(): void
    {
        $c = $this->challan('pending', '2026-10', '2026-10-10', 5000);

        $this->asAdmin()->get(route('admin.challan.view', $c->id))->assertOk()
            ->assertSee('Overdue')->assertSee('5,500')->assertSee('10045' . $c->fresh()->challan_no)->assertSee('Mark as paid');
    }

    // ------------------------------------------------------------------ parents & settings

    public function test_parent_password_reset(): void
    {
        $this->asAdmin()->post(route('admin.parent.update.password'), ['passwordId' => $this->parent->id, 'password' => 'ab'])
            ->assertSessionHasErrors('password');

        $this->asAdmin()->post(route('admin.parent.update.password'), ['passwordId' => $this->parent->id, 'password' => 'K7P2QX'])
            ->assertRedirect(route('admin.parent.show'));
        $this->assertTrue(Hash::check('K7P2QX', $this->parent->fresh()->password));
    }

    public function test_settings_save(): void
    {
        $id = GeneralSetting::first()->id;
        $this->asAdmin()->post(route('admin.general-settings.store'), [
            'setting_id' => $id, 'name' => 'Dunyapur Public School', 'account_no' => '999', 'one_bill_prefix' => '10046', 'late_fee_fine' => 300,
        ])->assertSessionHas('success');

        $this->assertSame('10046', GeneralSetting::first()->one_bill_prefix);
    }

    public function test_student_profile_lists_challans(): void
    {
        $this->challan('approved', '2026-10');
        $this->challan('pending', '2026-11');

        $this->asAdmin()->get(route('admin.view.admission', $this->student->id))->assertOk()
            ->assertSee('Ali Khan')->assertSee('Oct 2026')->assertSee('Nov 2026')->assertSee('2 in total');
    }

    // ------------------------------------------------------------------ admission: existing parent lookup

    public function test_parent_lookup_finds_father_whatever_the_stored_cnic_format(): void
    {
        // setUp parent is stored as plain digits (older records); add one stored with dashes
        Parant::create([
            'father_name' => 'Bilal Ahmed', 'father_nic' => '36302-1234567-1', 'password' => Hash::make('1234'),
            'occupation' => 'Farmer', 'address' => 'Dunyapur', 'contact_no' => '0300-7654321',
        ]);

        $this->ajax(route('admin.search-parant', ['q' => '35202-1234567-1']))
            ->assertOk()->assertJson(['statuscode' => 200, 'father_name' => 'Ahmed Khan', 'contact_no' => '0300-1234567', 'children' => ['Ali Khan']]);
        $this->ajax(route('admin.search-parant', ['q' => '36302-1234567-1']))
            ->assertOk()->assertJson(['statuscode' => 200, 'father_name' => 'Bilal Ahmed']);
        $this->ajax(route('admin.search-parant', ['q' => '3630212345671']))
            ->assertOk()->assertJson(['statuscode' => 200, 'father_name' => 'Bilal Ahmed']);
        $this->ajax(route('admin.search-parant', ['q' => '11111-2222222-3']))
            ->assertOk()->assertJson(['statuscode' => 404]);
    }

    public function test_new_admission_joins_an_existing_parent_stored_without_dashes(): void
    {
        $this->asAdmin()->post(route('admin.admission.submit'), [
            'name' => 'Sara Khan', 'date_of_birth' => '2017-03-01', 'gender' => 'Female',
            'father_name' => 'Ahmed Khan', 'father_nic' => '35202-1234567-1', 'password' => '',
            'contact_no' => '0300-1234567', 'occupation' => 'Teacher', 'address' => 'Dunyapur', 'status' => 'active',
            'academic_session_id' => $this->session->id, 'session_item_id' => $this->item->id,
            'previous_school' => 'None', 'fees_paid_last_institution' => 0, 'discount' => 0,
            'student_photo' => 'data:image/png;base64,' . base64_encode(str_repeat('x', 120)),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Parant::count(), 'a duplicate parent was created');
        $this->assertSame($this->parent->id, Student::where('name', 'Sara Khan')->value('parent_id'));
        $this->assertTrue(Hash::check('1234', $this->parent->fresh()->password), 'existing portal password changed');

        foreach (glob(public_path('upload/admission/stu_*')) as $f) {
            if (filemtime($f) >= time() - 60 && filesize($f) === 120) { @unlink($f); }
        }
    }

    public function test_admission_form_says_father_cnic(): void
    {
        $this->asAdmin()->get(route('admin.add.admission'))->assertOk()->assertSee('Father CNIC')->assertDontSee('Father NIC');
        $this->asAdmin()->get(route('admin.edit.admission', $this->student->id))->assertOk()->assertSee('Father CNIC')->assertDontSee('Father NIC');
    }
}
