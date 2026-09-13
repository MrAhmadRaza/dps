<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use App\Models\AcademicSession;
use App\Models\Student;

use App\Models\FeeChallan;

class DashboardController extends Controller
{
    // View Admission Form
    public function index(): View
    {
        $pageTitle = 'Dashboard'; 
        // $sessions = AcademicSession::latest()->count();
        // $students = Student::latest()->count();
        // $challans = FeeChallan::latest()->count();
        return view('backend.dashboard.dashboard',compact('pageTitle'));
    }
}
