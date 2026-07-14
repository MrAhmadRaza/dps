<?php

use Illuminate\Support\Facades\Route;
// Admin Routes
use App\Http\Controllers\Backend\Admin\DashboardController;
use App\Http\Controllers\Backend\Admin\AdmissionController;
use App\Http\Controllers\Backend\Admin\Auth\AdminSignInController;
use App\Http\Controllers\Backend\Admin\AcademicSessionController;
use App\Http\Controllers\Backend\Admin\FeeItemController;
use App\Http\Controllers\Backend\Admin\VoucherController;
use App\Http\Controllers\Backend\Admin\ChallanController;
use App\Http\Controllers\Backend\Admin\ParantController;
use App\Http\Controllers\Backend\Admin\CategoryController;
use App\Http\Controllers\Backend\Admin\HeadCategoryController;
use App\Http\Controllers\Backend\Admin\Setting;
// Parants
use App\Http\Controllers\Backend\Admin\Auth\ParantSignInController;
use App\Http\Controllers\Backend\Admin\Parant\DashboardController as ParantDashboardController;
use App\Http\Controllers\Backend\Admin\Parant\StudentController;
use App\Http\Controllers\Backend\Admin\Parant\FeeChallanController;
use App\Http\Controllers\Backend\Admin\Parant\ChallanPaymentController;


// Public Route Acccess
Route::get('/', function () {
   $academicSession = App\Models\AcademicSession::where('status', '=' , 'active')->latest()->count();
   $admissions      = App\Models\Student::latest()->count();
   $parents         = App\Models\Parant::latest()->count();
   $challans        = App\Models\FeeChallan::latest()->count();
    return view('frontend.home.index',compact('academicSession','admissions','parents','challans'));
})->name('/');  

// Admin Routes
Route::prefix('admin')->as('admin.')->group(function(){
    /** Authentication */
    Route::controller(AdminSignInController::class)->group(function () {
        Route::get('/sign-in', 'signIn')->name('signIn')->middleware('redirectIfLoggedIn');
        Route::post('/sign-in', 'signInSubmit')->name('signIn.submit');
        Route::post('/logout', 'logout')->name('logout');
    });

    /** Dashboard */
    Route::middleware(['auth', 'preventBackHistory'])->group(function () {
            Route::controller(DashboardController ::class)->group(function(){
                Route::get('/dashboard','index')->name('dashboard');
            });
    
        /** Admission */
        Route::controller(AdmissionController ::class)->group(function(){
            Route::get('/admission', 'index')->name('admission.index');
            Route::get('/admission/view/{id}','viewAdmission')->name('view.admission');
            Route::get('/admission/print/{id}', 'print')->name('print.admission');
            Route::get('/admission/voucher/{id}', 'printVoucher')->name('voucher.admission');
            Route::get('/admission/add-admission', 'addAdmission')->name('add.admission');
            Route::get('/search-portals','searchPortal')->name('search-portal');
            Route::get('/parent-portal-info/{id}','getInfo')->name('parent-portal-info');
            Route::post('/admission/class-section','getClassSection')->name('admission.class.section');
            Route::post('/admission', 'submitAdmission')->name('admission.submit');
            Route::get('/admission/edit-admission/{id}', 'editAdmission')->name('edit.admission');
            Route::post('/admission/update-admission/{id}', 'updateAdmission')->name('update.admission');
            Route::delete('/admission/destroy-admission/{id}', 'destroyAdmission')->name('destroy.admission');
        });

        /** Acadmic Session  */
        Route::controller(AcademicSessionController ::class)->group(function(){
            Route::get('/academic-session', 'index')->name('academic-session.index');
            Route::get('/academic-session/view/{id}', 'viewAcademicSession')->name('academic-session.view.index');
            Route::get('/academic-session/add', 'addAcademicSession')->name('academic-session.add');
            Route::get('/academic-session/class-section/{id?}', 'classSection')->name('academic-session.class-section');
            Route::post('/academic-session/store', 'storeAcademicSession')->name('academic-session.store');
            Route::post('/academic-session-item/store', 'storeAcademicSessionItem')->name('academic-session-item.store');
            Route::get('/academic-session/edit/{id}', 'editAcademicSession')->name('academic-session.edit');
            Route::get('/academic-session-item/edit/{id}', 'editAcademicSessionItem')->name('academic-session-item.edit');
            Route::post('/academic-session/update/{id}', 'updateAcademicSession')->name('academic-session.update');
            Route::post('/academic-session-item/update/{id}', 'updateAcademicSessionItem')->name('academic-session-item.update');
            Route::delete('/academic-session/destroy/{id}', 'destroyAcademicSession')->name('academic-session.destroy');
        });

        /** Voucher */
        Route::controller(VoucherController ::class)->group(function(){
            Route::get('/voucher', 'index')->name('voucher.index');
            Route::get('/voucher/view/{id}', 'viewVoucher')->name('voucher.view');
            Route::get('/voucher/print/{id}', 'printVoucher')->name('voucher.print');
            Route::get('/voucher/add', 'addVoucher')->name('voucher.add');
            Route::post('/voucher/store', 'storeVoucher')->name('voucher.store');
            Route::get('/voucher/edit/{id}', 'editVoucher')->name('voucher.edit');
            Route::post('/voucher/update/{id}', 'updateVoucher')->name('voucher.update');
            Route::delete('/voucher/destroy/{id}', 'destroyVoucher')->name('voucher.destroy');
        });

        /** Fee Challan */
        Route::controller(ChallanController ::class)->group(function(){
            Route::get('/parent/challans', 'index')->name('challan.index');
            Route::get('/parent', 'showParent')->name('parent.show');
            Route::get('/parent/password/{id}', 'setPassword')->name('parent.password');
            Route::get('/parent/challans/view/{id}','viewChallan')->name('challan.view');
            Route::get('/parent/challans/approved-chalan/{id}','approvedChallan')->name('challan.approved');
            Route::get('/parent/challans/pending-chalan/{id}','pendingChallan')->name('challan.pending');
            Route::get('/parent/challans/view-chalan/{id}','viewChallan')->name('challan.view');
            Route::delete('/parent/challan/destory/{id}','destroyChallan')->name('challan.destroy');
        });

        /** Parent */
        Route::controller(ParantController ::class)->group(function(){
            Route::get('/parent', 'showParent')->name('parent.show');
            Route::get('/parent/password/{id}', 'setPassword')->name('parent.password');
            Route::post('/parent/update-password', 'updatePassword')->name('parent.update.password');
        });

         /** Category  */
        Route::controller(CategoryController ::class)->group(function(){
            Route::get('/account/categories', 'index')->name('categories.index');
            Route::get('/account/categories/print', 'printCategory')->name('categories.print');
            Route::get('/account/categories/add', 'addCategory')->name('categories.add');
            Route::post('/account/categories/store', 'storeCategory')->name('categories.store');
            Route::get('/account/categories/headCategory/{id}', 'addHeadCategory')->name('categories.headcategory');
            Route::post('/account/categories/headStore', 'storeHeadCategory')->name('categories.headstore');  
            Route::get('/account/categories/edit/{id}', 'editCategory')->name('categories.edit');  
            Route::post('/account/categories/update/{id}', 'updateCategory')->name('categories.update'); 
            Route::delete('/account/categories/destroy/{id}', 'destroyCategory')->name('categories.destroy');  
            Route::get('/account/reports', 'Reports')->name('reports.print');  
            Route::get('accounts/reports/cashbook',  'cashbook')->name('reports.cashbook');         

        });

          /** Head Category  */
        Route::controller(HeadCategoryController ::class)->group(function(){
            Route::get('/account/categories/showHeadCategories/{id}', 'index')->name('categories.showHeadCategories');
            Route::get('/account/categories/category-head-print/{id}','printHeads')->name('categories.head.print');
            Route::get('/account/categories/headCategory/{id}', 'addHeadCategory')->name('categories.headcategory');
            Route::post('/account/categories/headStore', 'storeHeadCategory')->name('categories.headstore');  
            Route::get('/account/categories/editheadCategory/{id}', 'editHeadCategory')->name('categories.edithead'); 
            Route::post('/account/categories/updateheadCategory/{id}', 'updateHeadCategory')->name('categories.updatehead'); 
            Route::delete('/account/categories/destroyheadCategory/{id}', 'destroyHeadCategory')->name('categories.destroyhead');  
        });

        /** General Setting */
        Route::controller(Setting ::class)->group(function(){
            Route::get('/general-settings', 'index')->name('general-settings.index');
            Route::post('/general-settings', 'storeSettings')->name('general-settings.store');
        });
    });
});

// Parent Routes
// ===========================Parent Routes==============================================
Route::prefix('parent')->as('parent.')->group(function(){

    /** Authentication */
    Route::controller(ParantSignInController::class)->group(function () {
        Route::get('/sign-in', 'signIn')->name('signIn')->middleware('ParantRedirectLog');
        Route::post('/sign-in', 'signInSubmit')->name('signIn.submit');
        Route::post('/logout', 'logout')->name('logout');
    });

    /** Dashboard */
    Route::middleware(['auth:parents','preventBackHistory'])->group(function () {
        Route::controller(ParantDashboardController ::class)->group(function(){
            Route::get('/dashboard','index')->name('index');
        });

         /** Admission */
        Route::controller(StudentController ::class)->group(function(){
            Route::get('/student', 'index')->name('student.index');
            Route::get('/student/view/{id}','viewStudent')->name('view.student');
        });

          /** Fee Challan */
        Route::controller(FeeChallanController ::class)->group(function(){
            Route::get('/challan/fee-challan', 'index')->name('fee-challan');
            Route::get('/challan/fee-challans/ajax','getChallans')->name('fee-challans.ajax');
            Route::get('/print/challan-voucher/{voucher}', 'printChallan')->name('print.challan');
        });

           /** Fee Challan */
        Route::controller(ChallanPaymentController ::class)->group(function(){
            Route::post('/challan/upload',  'uploadChallan')->name('challan.upload');
            Route::get('/challan/view-challan/{id}', 'viewChallan')->name('view.challan');
        });

    });

});