<?php

namespace App\Http\Controllers\Backend\Admin;

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

class AdmissionController extends Controller
{
    // Show Admissions
    public function index(Request $request): View|JsonResponse
    {
        $pageTitle = 'Admission';
        if ($request->ajax()) {
            $query = Student::with('parent:id,father_name,contact_no')->latest();
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
                $viewRoute = route('admin.view.admission', $student->id);
                $printRoute = route('admin.print.admission',$student->id);
                $voucherRoute = route('admin.voucher.admission',$student->id);
                $editRoute = route('admin.edit.admission', $student->id);
                $deleteRoute = route('admin.destroy.admission', $student->id);
                return  view('backend.partials.admission-action',compact( 
                    'viewRoute','printRoute','voucherRoute','editRoute', 'deleteRoute'))->render();
            })
            ->rawColumns(['photo_path','academic_session_id','created_at','action'])
            ->make(true);
        }
        return view('backend.admission.showAdmission', compact('pageTitle'));
    }

    // Add Admission Form
    public function addAdmission(): View
    {
        $pageTitle = 'Add Admission'; 
        $sessions = AcademicSession::where('status','active')->get();
        return view('backend.admission.addAdmission',compact('sessions','pageTitle'));
    }
    // Portal Id 
    public function searchPortal(Request $request)
    {
        $search = $request->q;
        $portals = Parant::where('portal_id', 'LIKE', "%$search%")->get();
        $result = [];
        foreach ($portals as $portal) {
            $result[] = [
                'id' => $portal->id,
                'text' => $portal->portal_id
            ];
        }
        return response()->json($result);
    }

    // Portal Info Get
    public function getInfo(int $id)
    {
        $parent = Parant::find($id);
        if(!$parent){
            return response()->json(['statuscode' => 404]);
        }
        return response()->json([
            'statuscode'  => 200,
            'father_name' => $parent->father_name ?? '',
            'father_nic'  => $parent->father_nic ?? '',
            'mother_name' => $parent->mother_name ?? '',
            'occupation'  => $parent->occupation ?? '',
            'income'      => $parent->income ?? '',
            'address'     => $parent->address ?? '',
            'contact_no'  => $parent->contact_no ?? '',
        ]);
    }

    // Ajax with  Session Id fetch 
    public function getClassSection(Request $request)
    {
        $session_items = SessionItem::where('academic_session_id', $request->academic_session_id)->get();
        if($session_items->isEmpty())
        {
            return response()->json([
                'status_code' => 404,
                'message' => 'No record found',
                'session_items' => []
            ]);
        }
        return response()->json([
            'status_code' => 200,
            'session_items' => $session_items
        ]);
    }

    // View Admission
    public function viewAdmission(int $id): View
    {
        $pageTitle = 'View Admission'; 
        $student = Student::with(['parent', 'guardian','sibling'])->findOrFail($id);
        return view('backend.admission.viewAdmission', compact('student','pageTitle'));
    }

    // Print Form
    public function print(int $id): View
    {
        $student = Student::with(['parent', 'guardian','sibling','sessionItem'])->findOrFail($id);
        return view('backend.admission.print', compact('student'));
    }

    // Voucher Print
    public function printVoucher(int $id): View
    {
        try{
            $student = Student::findOrFail($id);
            $voucher = Voucher::with(['items','sessionItem'])
            ->where('academic_session_id', $student->academic_session_id)
            ->where('session_item_id', $student->session_item_id)
            ->first();
            return view('backend.admission.voucher.voucher-print', compact('student','voucher'));
        }catch (\Exception $e) {
            \Log::error('Print Voucher Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // Store Admisison Details
    public function submitAdmission(Request $request): RedirectResponse
    {
        $parentExists = Parant::where('id', $request->portal_id)->exists();
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'b_form_no' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'caste' => 'nullable|string|max:50',
            'domicile' => 'nullable|string|max:50',
            'portal_id' => 'required',
            'password' => [
                $parentExists ? 'nullable' : 'required',
                'string',
                'size:4',
                'regex:/^[A-Za-z0-9]+$/',
            ],
            'father_name' => 'required|string|max:255',
            'father_nic' => is_numeric($request->portal_id) ? 'required|digits:13' : 'required|unique:parents,father_nic|required|digits:13',
            'contact_no' =>  is_numeric($request->portal_id)  ? 'required|digits:11'  : 'required|digits:11|unique:parents,contact_no' ,
            'mother_name' => 'nullable|string|max:255',
            'occupation' => 'required|string|max:255',
            'income'       => 'nullable|numeric|min:0',
            'address' => 'required|string',
            'guardian_name' => 'nullable|string|max:255',
            'relation' => 'nullable|string|max:50',
            'guardian_nic' => 'nullable|digits:13',
            'guardian_contact_no' => 'nullable|digits:11|unique:guardians,contact_no',
            'sibling_name' => 'nullable|string',
            'sibling_class' => 'nullable|string',
            'sibling_section' => 'nullable|string',
            'sibling_in_dps' => 'nullable|integer|min:0',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'session_item_id'     => 'required|exists:session_items,id',
            'previous_school' => 'required|string|max:255',
            'last_fee_paid_upto' => 'nullable|date',
            'fees_paid_last_institution' => 'required|in:0,1',
            'games_sports' => 'nullable|string',
            'extra_curricular' => 'nullable|string',
            'admission_date' => 'nullable|date',
            'student_photo' => 'required|string|min:100',
            'admission_fee'       => 'nullable|numeric|min:0',
            'tuition_fee'         => 'nullable|numeric|min:0',
            'stationary_fee'      => 'nullable|numeric|min:0',
            'library_fee'         => 'nullable|numeric|min:0',
            'sports_fund'         => 'nullable|numeric|min:0',
            'security_deposit'    => 'nullable|numeric|min:0',
            'development_fund'    => 'nullable|numeric|min:0',
            'misc_charges'        => 'nullable|numeric|min:0',
            'total_fee_paid'      => 'nullable|numeric|min:0',
            'receipt_no'          => 'nullable|string|max:50|regex:/^[A-Za-z0-9\-\/]+$/',
            'fee_paid_date'       => 'nullable|date',
        ]);
        //Photo Path
        $photoPath = null;
        // Database transaction
        DB::transaction(function() use($validated, $photoPath ){
           // check the portal id exit or not
            $getParentPortal =  Parant::where('id', $validated['portal_id'])->first();
            if(!$getParentPortal)
            {
                // Parent
                $parent = Parant::create([
                    'portal_id' => $validated['portal_id'],
                    'password' => Hash::make($validated['password']),
                    'father_name'=>$validated['father_name'],
                    'father_nic'=>$validated['father_nic'] ?? null,
                    'mother_name'=>$validated['mother_name'] ?? null,
                    'occupation'=>$validated['occupation'],
                    'income'    => $validated['income'],
                    'address'=>$validated['address'] ?? null,
                    'contact_no'=>$validated['contact_no'],
                ]);
            }else{
                $parentId = $getParentPortal->id ?? NULL;
            }
              // Handle photo
            if (!empty($validated['student_photo']) && str_contains($validated['student_photo'], ';base64,')) {
                $img = explode(";base64,", $validated['student_photo']);
                $ext = explode('/', explode(':', $img[0])[1])[1] ?? 'jpg';
                $filename = 'stu_' . time() . '.' . $ext;
                // Ensure upload directory exists
                $uploadPath = public_path('upload/admission');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                // Save file into public/upload
                file_put_contents(
                    $uploadPath . '/' . $filename,
                    base64_decode($img[1])
                );
                // Save relative path in DB
                $photoPath = 'upload/admission/' . $filename;
            }
            // Student
            $student = Student::create([
                'parent_id' => $parent->id ?? $parentId,
                'name'=>$validated['name'],
                'date_of_birth'=>$validated['date_of_birth'],
                'gender'=>$validated['gender'],
                'b_form_no'=>$validated['b_form_no'] ?? null,
                'religion'=>$validated['religion'] ?? null,
                'caste'=>$validated['caste'] ?? null,
                'domicile'=>$validated['domicile'] ?? null,
                'academic_session_id' => $validated['academic_session_id'] ?? null,
                'session_item_id' => $validated['session_item_id'] ?? null,
                'previous_school'=> $validated['previous_school'] ?? null,
                'last_fee_paid_upto'=>$validated['last_fee_paid_upto'] ?? null,
                'fees_paid_last_institution'=>$validated['fees_paid_last_institution'],
                'games_sports'=>$validated['games_sports'] ?? null,
                'extra_curricular'=>$validated['extra_curricular'] ?? null,
                'admission_date'=>$validated['admission_date'] ?? null,
                'photo_path'=>$photoPath,
                'admission_fee'       => $validated['admission_fee'] ?? 0,
                'tuition_fee'         => $validated['tuition_fee'] ?? 0,
                'stationary_fee'      => $validated['stationary_fee'] ?? 0,
                'library_fee'         => $validated['library_fee'] ?? 0,
                'sports_fund'         => $validated['sports_fund'] ?? 0,
                'security_deposit'    => $validated['security_deposit'] ?? 0,
                'development_fund'    => $validated['development_fund'] ?? 0,
                'misc_charges'        => $validated['misc_charges'] ?? 0,
                'total_fee_paid'      => $validated['total_fee_paid'] ?? 0,
                'receipt_no'          => $validated['receipt_no'] ?? null,
                'fee_paid_date'       => $validated['fee_paid_date'] ?? null,
                'created_by'=>Auth::id()
            ]);
            // Guardian
            if(!empty($validated['guardian_name'])){
                Guardian::create([
                    'student_id'=>$student->id,
                    'guardian_name'=>$validated['guardian_name'],
                    'relation'=>$validated['relation'] ?? 'Other',
                    'guardian_nic'=>$validated['guardian_nic'] ?? null,
                    'contact_no'=>$validated['guardian_contact_no'] ?? null
                ]);
            }
            // Siblings
             Sibling::create([
                'student_id'=>$student->id,
                'sibling_name'=>$validated['sibling_name'] ?? null,
                'sibling_class'=>$validated['sibling_class'] ?? null,
                'sibling_section'=>$validated['sibling_section'] ?? null,
                'sibling_in_dps'=>$validated['sibling_in_dps'] ?? 0,
            ]);
        });
        return redirect()->route('admin.add.admission')->with('success','Admission saved successfully!');
    }

    // Edit Admission
    public function editAdmission(int $id): View
    {
        $pageTitle = 'Edit Admission'; 
        $student = Student::with(['parent', 'guardian','sibling'])->findOrFail($id);
        $sessions = AcademicSession::where('status','active')->get();
        return view('backend.admission.editAdmission', compact('sessions','student','pageTitle'));
    }

    // Update Admission
    public function updateAdmission(Request $request, int $id): RedirectResponse
    {
        $student = Student::with('parent')->findOrFail($id);
        $guardian = $student->guardian()->first();
        $sibling = $student->sibling()->first();
        $guardianId = $guardian?->id;
        // Validation
        $parentId = is_numeric($request->portal_id) ? $request->portal_id : null;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'b_form_no' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'caste' => 'nullable|string|max:50',
            'domicile' => 'nullable|string|max:50',
            'portal_id' => 'required',
            'password' =>      'nullable','string','size:4','regex:/^[A-Za-z0-9]+$/',
            'father_name' => 'required|string|max:255',
            'father_nic' => [
                'required',
                'digits:13',
                Rule::unique('parents','father_nic')->ignore($parentId),
            ],
            'mother_name' => 'nullable|string|max:255',
            'occupation' => 'required|string|max:255',
            'income'       => 'nullable|numeric|min:0',
           'contact_no' => [
                'required',
                'digits:11',
                Rule::unique('parents','contact_no')->ignore($parentId),
            ],
            'address' => 'required|string',
            'guardian_name' => 'nullable|string|max:255',
            'relation' => 'nullable|string|max:50',
            'guardian_nic' => 'nullable|digits:13',
            'guardian_contact_no' =>  [
                'nullable',
                'digits:11',
                Rule::unique('guardians','contact_no')->ignore($guardianId),
            ],
            'sibling_name' => 'nullable|string',
            'sibling_class' => 'nullable|string',
            'sibling_section' => 'nullable|string',
            'sibling_in_dps' => 'nullable|integer|min:0',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'session_item_id'     => 'required|exists:session_items,id',
            'previous_school' => 'required|string|max:255',
            'last_fee_paid_upto' => 'nullable|date',
            'fees_paid_last_institution' => 'required|in:0,1',
            'games_sports' => 'nullable|string',
            'extra_curricular' => 'nullable|string',
            'student_photo' => 'nullable|string|min:100',
            'admission_date' => 'nullable|date',
            'admission_fee'    => 'nullable|numeric|min:0',
            'tuition_fee'      => 'nullable|numeric|min:0',
            'stationary_fee'   => 'nullable|numeric|min:0',
            'library_fee'      => 'nullable|numeric|min:0',
            'sports_fund'      => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'development_fund' => 'nullable|numeric|min:0',
            'misc_charges'     => 'nullable|numeric|min:0',
            'total_fee_paid'   => 'nullable|numeric|min:0',
            'receipt_no' => 'nullable|string|max:50|regex:/^[A-Za-z0-9\-\/]+$/',
            'fee_paid_date' => 'nullable|date',
        ]);

        // Logo Path 
        $photoPath = null;
        // Database transaction
        DB::transaction(function() use ($validated, $student, $photoPath , $guardian, $sibling) {
            // Handle photo update
            $photoPath = $student->photo_path; // Keep old photo by default
            if (!empty($validated['student_photo']) && str_contains($validated['student_photo'], ';base64,')) {
                // Delete old photo if exists
                if ($student->photo_path) {

                    $oldPhotoFullPath = public_path($student->photo_path);

                    if (file_exists($oldPhotoFullPath)) {
                        unlink($oldPhotoFullPath);
                    }
                }
                // Process new image
                $img = explode(";base64,", $validated['student_photo']);
                $ext = explode('/', explode(':', $img[0])[1])[1] ?? 'jpg';
                $filename = 'stu_' . time() . '.' . $ext;
                $uploadPath = public_path('upload/admission');
                // Create folder if not exists
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                // Save new file
                file_put_contents(
                    $uploadPath . '/' . $filename,
                    base64_decode($img[1])
                );
                // Store relative path in DB
                $photoPath = 'upload/admission/' . $filename;
            }
            //Check Password if exit or not
            $password = !empty($validated['password']) ? Hash::make($validated['password']) : $student->parent->password;
            //Check the portal id exit or not
            $getParentPortal =  Parant::with('students:id,parent_id')->find($validated['portal_id']);
            // Update Parent
            if($getParentPortal)
            {
                $getParentPortal->update([
                    'password'    => $password,           
                ]);
                $parentId =  $getParentPortal->id;
            }elseif(!$getParentPortal){
                // new parent create
                $parent = Parant::create([
                    'portal_id'   => $validated['portal_id'],
                    'password'    => $password,
                    'father_name' => $validated['father_name'],
                    'father_nic'  => $validated['father_nic'] ?? null,
                    'mother_name' => $validated['mother_name'] ?? null,
                    'occupation'  => $validated['occupation'],
                    'income'      => $validated['income'],
                    'address'     => $validated['address'] ?? null,
                    'contact_no'  => $validated['contact_no'],
                ]);
                $parentId = $parent->id;
            }else{
                $parentId =  $getParentPortal->id;
            }
            // Update Student
            $student->update([
                'parent_id' =>  $parentId,
                'name' => $validated['name'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'b_form_no' => $validated['b_form_no'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'caste' => $validated['caste'] ?? null,
                'domicile' => $validated['domicile'] ?? null,
                'academic_session_id' => $validated['academic_session_id'] ?? null,
                'session_item_id' => $validated['session_item_id'] ?? null,
                'previous_school' => $validated['previous_school'] ?? null,
                'last_fee_paid_upto' => $validated['last_fee_paid_upto'] ?? null,
                'fees_paid_last_institution' => $validated['fees_paid_last_institution'],
                'games_sports' => $validated['games_sports'] ?? null,
                'extra_curricular' => $validated['extra_curricular'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'photo_path' => $photoPath,
                'admission_fee'       => $validated['admission_fee'] ?? 0,
                'tuition_fee'         => $validated['tuition_fee'] ?? 0,
                'stationary_fee'      => $validated['stationary_fee'] ?? 0,
                'library_fee'         => $validated['library_fee'] ?? 0,
                'sports_fund'         => $validated['sports_fund'] ?? 0,
                'security_deposit'    => $validated['security_deposit'] ?? 0,
                'development_fund'    => $validated['development_fund'] ?? 0,
                'misc_charges'        => $validated['misc_charges'] ?? 0,
                'total_fee_paid'      => $validated['total_fee_paid'] ?? 0,
                'receipt_no'          => $validated['receipt_no'] ?? null,
                'fee_paid_date'       => $validated['fee_paid_date'] ?? null,
                'created_by'=>Auth::id()
            ]);
            // Update Guardian
            if (!empty($validated['guardian_name'])) {
                if ($guardian) {
                    $guardian->update([
                        'guardian_name' => $validated['guardian_name'],
                        'relation' => $validated['relation'] ?? 'Other',
                        'guardian_nic' => $validated['guardian_nic'] ?? null,
                        'contact_no' => $validated['guardian_contact_no'] ?? null
                    ]);
                } else {
                    $student->guardian()->create([
                        'guardian_name' => $validated['guardian_name'],
                        'relation' => $validated['relation'] ?? 'Other',
                        'guardian_nic' => $validated['guardian_nic'] ?? null,
                        'contact_no' => $validated['guardian_contact_no'] ?? null
                    ]);
                }
            }
            // Siblings
            if($sibling)
                {
                    $sibling->update([
                       'student_id'=>$student->id,
                       'sibling_name'=>$validated['sibling_name'] ?? null,
                       'sibling_class'=>$validated['sibling_class'] ?? null,
                       'sibling_section'=>$validated['sibling_section'] ?? null,
                       'sibling_in_dps'=>$validated['sibling_in_dps'] ?? 0,
                   ]);
                }
        });

        return redirect()->route('admin.admission.index')->with('success', 'Admission updated successfully!');
    }

    // Delete Admission
    public function destroyAdmission(int $id): RedirectResponse
    {
        $student = Student::findOrFail($id);
        DB::transaction(function () use ($student) {
            // Delete student photo from public folder
            if ($student->photo_path) {
                $photoFullPath = public_path($student->photo_path);
                if (file_exists($photoFullPath)) {
                    unlink($photoFullPath);
                }
            }
            // Delete related parent
            if ($student->parent) {
                $student->parent->delete();
            }
            // Delete related guardians
            if ($student->guardian) {
                    $student->guardian->delete();
            }
            // Delete related siblings
            if ($student->sibling) {
                    $student->sibling->delete();
            }
            // Delete student record
            $student->delete();
        });
        return redirect()->route('admin.admission.index')->with('success', 'Admission deleted successfully!');
    }
}
