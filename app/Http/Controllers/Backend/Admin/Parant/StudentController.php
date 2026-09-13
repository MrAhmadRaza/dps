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
                            ->where('status','active')
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
                }else{
                      return '<img src="' . asset('default/dummy.png') . '" 
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

            ->filterColumn('academic_session_id', function ($query, $keyword) {
                $query->whereHas('academicSession', function ($q) use ($keyword) {
                    $q->where('session_name', 'LIKE', "%{$keyword}%");
                });
            })

            ->addColumn('class', function (Student $student) {
                return $student->sessionItem?->class ?? 'N/A';
            })

            ->filterColumn('class', function ($query, $keyword) {
                $query->whereHas('sessionItem', function ($q) use ($keyword) {
                    $q->where('class', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('section', function (Student $student) {
                return $student->sessionItem?->section ?? 'N/A';
            })

            ->filterColumn('section', function ($query, $keyword) {
                $query->whereHas('sessionItem', function ($q) use ($keyword) {
                    $q->where('section', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('admission_date', function (Student $student) {
                return Carbon::parse($student->admission_date)->format('d M Y') ?? 'N/A';
            })

            ->filterColumn('admission_date', function ($query, $keyword) {
                $query->whereRaw(
                    "DATE_FORMAT(admission_date, '%d %b %Y') LIKE ?",
                    ["%{$keyword}%"]
                );
            })

           ->addColumn('status', function (Student $student) {
                if ( $student->status === 'active') {
                    return '<span class="badge bg-primary px-3 py-2 ">
                                <i class="fas fa-check-circle me-1"></i> Active
                            </span>';
                } else {
                    return '<span class="badge bg-danger px-3 py-2 ">
                                <i class="fas fa-times-circle me-1"></i> Inactive
                            </span>';
                }
            })

            ->filterColumn('status', function ($query, $keyword) {
                $keyword = strtolower(trim($keyword));

                if (str_contains($keyword, 'active') && !str_contains($keyword, 'in')) {
                    $query->where('status', 'active'); 
                } 
                elseif (str_contains($keyword, 'inactive') || str_contains($keyword, 'in active') || $keyword === 'in') {
                    $query->where('status', 'inactive');
                } 
                else {
                    // normal number search (0 or 1)
                    $query->where('status', 'like', "%{$keyword}%");
                }
            })
            ->addColumn('action', function (Student $student) {
                $viewRoute = route('parent.view.student', $student->id);
                return  view('backend.parent.partials.student-action',compact('viewRoute'))->render();
            })
            ->rawColumns(['photo_path','academic_session_id','status','action'])
            ->make(true);
        }
        return view('backend.parent.students.show-student', compact('pageTitle'));
    }

    // View Student
    public function viewStudent(int $id): View
    {
        $pageTitle = 'View Student';
        $student = Student::with(['parent','guardian','sibling','academicSession','sessionItem'])->findOrFail($id);
        $voucher = null;
        if ($student->academic_session_id && $student->session_item_id) {
            $voucher = Voucher::with('items')
                ->where('academic_session_id', $student->academic_session_id)
                ->where('session_item_id', $student->session_item_id)
                ->first();
        }
        return view('backend.parent.students.view-student',compact('student', 'voucher', 'pageTitle'));
    }

}
