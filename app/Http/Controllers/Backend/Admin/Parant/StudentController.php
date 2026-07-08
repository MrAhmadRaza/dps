<?php

namespace App\Http\Controllers\Backend\Admin\Parant;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Models\AcademicSession;
use App\Models\SessionItem;
use App\Models\Parant;
use App\Models\Voucher;
use App\Models\Guardian;
use App\Models\Sibling;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use DataTables;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    // Show Students
    public function index(Request $request): View|JsonResponse
    {
        $pageTitle = 'Student';
        $parentId = auth()->guard('parents')->user()->id;
        if ($request->ajax()) {
            $query = Student::with('parent:id,father_name,contact_no')
                            ->where('parent_id', $parentId)
                            ->latest();
        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('photo_path', function (Student $student) {
               if ($student->photo_path && file_exists(public_path($student->photo_path))) {
                return '<img src="' . asset($student->photo_path) . '" 
                        alt="Student Photo" 
                        class="rounded-circle" 
                        width="50" 
                        height="50" 
                        style="object-fit: cover; border: 1px solid #dee2e6;">';
                }
            })

             ->addColumn('academic_session_id', function (Student $student) {
                   return  $student->academicSession->session_name ?? 'N/A';
            })

            ->addColumn('admission_date', function (Student $student) {
                return Carbon::parse($student->admission_date)->format('d M Y') ?? 'N/A';
            })

            ->addColumn('created_at', function (Student $student) {
                return Carbon::parse($student->created_at)->format('d M Y') ?? 'N/A';
            })
        
            ->filterColumn('created_at', function ($query, $keyword) {
                $keyword = trim($keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->whereRaw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '+01:00'), '%d %b %Y, %h:%i %p') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%d %b %Y') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%M %Y') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%d %M') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%h:%i %p') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%$keyword%"]);
                });
            })
            ->addColumn('action', function (Student $student) {
                $viewRoute = route('parent.view.student', $student->id);
                return  view('backend.parent.partials.student-action',compact('viewRoute'))->render();
            })
            ->rawColumns(['photo_path','academic_session_id','created_at','action'])
            ->make(true);
        }
        return view('backend.parent.students.show-student', compact('pageTitle'));
    }

    // View Student
    public function viewStudent(int $id): View
    {
        $pageTitle = 'View Student'; 
        $student = Student::with(['parent', 'guardian','sibling'])->findOrFail($id);
        return view('backend.parent.students.view-student', compact('student','pageTitle'));
    }

}
