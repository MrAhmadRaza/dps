<?php

/*
|--------------------------------------------------------------------------
| Admin (staff) portal routes
|--------------------------------------------------------------------------
| Loaded from bootstrap/app.php with the "web" middleware group. Everything
| the staff portal serves lives here, separate from the public site
| (routes/web.php) and the parent portal (routes/parent.php).
|
| URLs and route names are unchanged from before the revamp, so bookmarks
| and the public site's "Staff Login" link keep working.
*/

use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\AdmissionController;
use App\Http\Controllers\Admin\Auth\AdminSignInController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChallanController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeadCategoryController;
use App\Http\Controllers\Admin\ParantController;
use App\Http\Controllers\Admin\Setting;
use App\Http\Controllers\Admin\VoucherController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->as('admin.')->group(function () {

    Route::redirect('/', '/admin/dashboard')->name('home');

    /** Authentication */
    Route::controller(AdminSignInController::class)->group(function () {
        Route::get('/sign-in', 'signIn')->name('signIn')->middleware('redirectIfLoggedIn');
        Route::post('/sign-in', 'signInSubmit')->name('signIn.submit')->middleware('redirectIfLoggedIn');
        Route::post('/logout', 'logout')->name('logout');
    });

    Route::middleware(['auth', 'preventBackHistory'])->group(function () {

        /** Dashboard */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /** Admission */
        Route::controller(AdmissionController::class)->group(function () {
            Route::get('/admission', 'index')->name('admission.index');
            Route::get('/admission/view/{id}', 'viewAdmission')->name('view.admission');
            Route::get('/admission/print/{id}', 'print')->name('print.admission');
            Route::get('/admission/voucher/{id}', 'printVoucher')->name('voucher.admission');
            Route::get('/admission/{studentId}/challans', 'studentChallans')->name('admission.challans');
            Route::get('/admission/{studentId}/challan.print', 'challanPrint')->name('challan.print');
            Route::get('/admission/add-admission', 'addAdmission')->name('add.admission');
            Route::get('/search-parant', 'searchParant')->name('search-parant');
            Route::post('/admission/class-section', 'exitClassSection')->name('admission.class.section');
            Route::post('/admission/amounts', 'getVoucherAmounts')->name('admission.amounts');
            Route::post('/admission', 'submitAdmission')->name('admission.submit');
            Route::get('/admission/edit-admission/{id}', 'editAdmission')->name('edit.admission');
            Route::post('/admission/update-admission/{id}', 'updateAdmission')->name('update.admission');
            Route::delete('/admission/destroy-admission/{id}', 'destroyAdmission')->name('destroy.admission');
        });

        /** Academic session */
        Route::controller(AcademicSessionController::class)->group(function () {
            Route::get('/academic-session', 'index')->name('academic-session.index');
            Route::get('/academic-session/view/{id}', 'viewAcademicSession')->name('academic-session.view.index');
            Route::get('/academic-session/add-academic-session', 'addAcademicSession')->name('academic-session.add');
            Route::get('/academic-session/class-section/{id?}', 'classSection')->name('academic-session.class-section');
            Route::post('/academic-session/store', 'storeAcademicSession')->name('academic-session.store');
            Route::post('/academic-session-item/store', 'storeAcademicSessionItem')->name('academic-session-item.store');
            Route::get('/academic-session/edit/{id}', 'editAcademicSession')->name('academic-session.edit');
            Route::get('/academic-session-item/edit/{id}', 'editAcademicSessionItem')->name('academic-session-item.edit');
            Route::post('/academic-session/update/{id}', 'updateAcademicSession')->name('academic-session.update');
            Route::post('/academic-session-item/update/{id}', 'updateAcademicSessionItem')->name('academic-session-item.update');
            Route::delete('/academic-session/destroy/{id}', 'destroyAcademicSession')->name('academic-session.destroy');
        });

        /** Fee voucher (fee structure per class) */
        Route::controller(VoucherController::class)->group(function () {
            Route::get('/voucher', 'index')->name('voucher.index');
            Route::get('/voucher/view/{id}', 'viewVoucher')->name('voucher.view');
            Route::get('/voucher/print/{id}', 'printVoucher')->name('voucher.print');
            Route::get('/voucher/add-voucher', 'addVoucher')->name('voucher.add');
            Route::post('/voucher/class-section', 'exitClassSection')->name('voucher.class.section');
            Route::post('/voucher/amounts', 'getVoucherAmounts')->name('voucher.amounts');
            Route::post('/voucher/store', 'storeVoucher')->name('voucher.store');
            Route::get('/voucher/edit/{id}', 'editVoucher')->name('voucher.edit');
            Route::post('/voucher/update/{id}', 'updateVoucher')->name('voucher.update');
            Route::delete('/voucher/destroy/{id}', 'destroyVoucher')->name('voucher.destroy');
        });

        /** Fee challans */
        Route::controller(ChallanController::class)->group(function () {
            Route::get('/parent/challans', 'index')->name('challan.index');
            Route::post('/parent/custom/generate', 'customGenerate')->name('challan.custom.generate');
            Route::get('/parent/challans/view-chalan/{id}', 'viewChallan')->name('challan.view');
            // State changes are POST only (they used to be GET links).
            Route::post('/parent/challans/approved-chalan/{id}', 'approvedChallan')->name('challan.approved');
            Route::post('/parent/challans/pending-chalan/{id}', 'pendingChallan')->name('challan.pending');
            Route::delete('/parent/challan/destory/{id}', 'destroyChallan')->name('challan.destroy');
        });

        /** Parent accounts (password reset) */
        Route::controller(ParantController::class)->group(function () {
            Route::get('/parent', 'showParent')->name('parent.show');
            Route::get('/parent/password/{id}', 'setPassword')->name('parent.password');
            Route::post('/parent/update-password', 'updatePassword')->name('parent.update.password');
        });

        /** Account categories and reports */
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/account/categories', 'index')->name('categories.index');
            Route::get('/account/categories/print', 'printCategory')->name('categories.print');
            Route::get('/account/categories/add', 'addCategory')->name('categories.add');
            Route::post('/account/categories/store', 'storeCategory')->name('categories.store');
            Route::get('/account/categories/edit/{id}', 'editCategory')->name('categories.edit');
            Route::post('/account/categories/update/{id}', 'updateCategory')->name('categories.update');
            Route::delete('/account/categories/destroy/{id}', 'destroyCategory')->name('categories.destroy');
            Route::get('/account/reports', 'Reports')->name('reports.print');
            Route::get('accounts/reports/cashbook', 'cashbook')->name('reports.cashbook');
        });

        /** Head categories (entries under a category) */
        Route::controller(HeadCategoryController::class)->group(function () {
            Route::get('/account/categories/showHeadCategories/{id}', 'index')->name('categories.showHeadCategories');
            Route::get('/account/categories/category-head-print/{id}', 'printHeads')->name('categories.head.print');
            Route::get('/account/categories/headCategory/{id}', 'addHeadCategory')->name('categories.headcategory');
            Route::post('/account/categories/headStore', 'storeHeadCategory')->name('categories.headstore');
            Route::get('/account/categories/editheadCategory/{id}', 'editHeadCategory')->name('categories.edithead');
            Route::post('/account/categories/updateheadCategory/{id}', 'updateHeadCategory')->name('categories.updatehead');
            Route::delete('/account/categories/destroyheadCategory/{id}', 'destroyHeadCategory')->name('categories.destroyhead');
        });

        /** General settings */
        Route::controller(Setting::class)->group(function () {
            Route::get('/general-settings', 'index')->name('general-settings.index');
            Route::post('/general-settings', 'storeSettings')->name('general-settings.store');
        });
    });
});
