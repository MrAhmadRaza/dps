<?php

namespace App\Http\Controllers\Backend\Admin\Parant;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Models\Parant;
use App\Models\Voucher;
use App\Models\FeeChallan;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use DataTables;
use Illuminate\Support\Facades\Auth;

class FeeChallanController extends Controller
{
    // View Challan 
    public function index(): View
    {
        $pageTitle = 'Challan';
        $parent = auth()->guard('parents')->user()->id;
        $students =  Student::where('parent_id',$parent)->get();
        return view('backend.parent.fee-challan.show Challan',compact('pageTitle','students'));
    }

    // Get Challan for Student
    public function getChallans(Request $request): JsonResponse
    {
        $data = FeeChallan::with([
            'voucher',
            'student.academicSession',
            'student.sessionItem'
        ])
        ->where('student_id', $request->student_id)
        ->latest()
        ->get();
        return response()->json($data);
    }

    // Fee Challan Print
    public function printChallan(int $id): View
    {
        $voucher = FeeChallan::with(['voucher.items','student.parent','student.sessionItem'])->findOrFail($id);
        $student = $voucher->student;
        return view('backend.parent.fee-challan.challan-print', compact('voucher','student'));
    }
}
