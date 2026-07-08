<?php

namespace App\Http\Controllers\Backend\Admin\Parant;

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
        $students = Student::with('parent')->where('parent_id',auth()->guard('parents')->user()->id)->latest()->count();
        $parent = auth()->user(); // ya guard('parent') agar custom guard use kar rahe ho
        $challans = FeeChallan::whereHas('student', function ($q) use ($parent) {
            $q->where('parent_id', $parent->id);
        })->count();
        return view('backend.parent.dashboard.index',compact('pageTitle','students','challans'));
    }
}
