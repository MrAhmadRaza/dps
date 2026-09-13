<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Helpers\FeeChallanHelper;
use App\Models\Student;
use App\Models\AcademicSession;
use App\Models\SessionItem;
use App\Models\Parant;
use App\Models\FeeChallan;
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
                $viewRoute = route('admin.view.admission', $student->id);
                $printRoute = route('admin.print.admission',$student->id);
                $voucherRoute = route('admin.voucher.admission',$student->id);
                $editRoute = route('admin.edit.admission', $student->id);
                $deleteRoute = route('admin.destroy.admission', $student->id);
                return  view('backend.partials.admission-action',compact( 
                    'viewRoute','printRoute','voucherRoute','editRoute', 'deleteRoute'))->render();
            })
            ->rawColumns(['photo_path','academic_session_id','status','action'])
            ->make(true);
        }
        return view('backend.admission.showAdmission', compact('pageTitle'));
    }

    // Add Admission Form
    public function addAdmission(): View
    {
        $pageTitle = 'Add Admission'; 
        $sessions = AcademicSession::where('status','=' , 'active')->get();
        return view('backend.admission.addAdmission',compact('sessions','pageTitle'));
    }
    // Parent Search
    public function searchParant(Request $request)
    {
        $search = $request->q;
        $parent = Parant::query()
            ->where('father_nic', $search)
            ->first([
                'id',
                'father_name',
                'father_nic',
                'mother_name',
                'occupation',
                'income',
                'address',
                'contact_no',
            ]);
        if (!$parent) {
            return response()->json([
                'statuscode' => 404,
                'message' => 'Parent not found',
            ]);
        }

        return response()->json([
            'statuscode'  => 200,
            'father_name' => $parent->father_name,
            'father_nic'  => $parent->father_nic,
            'mother_name' => $parent->mother_name,
            'occupation'  => $parent->occupation,
            'income'      => $parent->income,
            'address'     => $parent->address,
            'contact_no'  => $parent->contact_no,
        ]);
    }
  
   // View Admission
    public function viewAdmission(int $id): View
    {
        $pageTitle = 'View Admission';
        
        // Student with related data loaded
        $student = Student::with([
            'parent', 
            'guardian', 
            'sibling', 
            'academicSession', 
            'sessionItem'
        ])->findOrFail($id);

        // Fetch Voucher based on student's session & session item
        $voucher = Voucher::with('items')
            ->where('academic_session_id', $student->academic_session_id)
            ->where('session_item_id', $student->session_item_id)
            ->first();

        return view('backend.admission.viewAdmission', compact('student', 'pageTitle', 'voucher'));
    }
 

    public function studentChallans(Request $request, int $studentId): View|JsonResponse
    {
        if ($request->ajax()) {
            // Query fee_challan_items snapshot instead of voucher items
            $query = FeeChallan::with([
                'items',
                'student',
                'payment',
            ])
            ->where('student_id', $studentId)
            ->latest('id');

            return datatables()->of($query)
                ->addIndexColumn()

                 // Voucher Payment Proof / Snapshot Items Badge
                ->addColumn('voucher_image', function ($row) {
                    if (
                        $row->payment &&
                        $row->payment->voucher_image &&
                        file_exists(public_path($row->payment->voucher_image))
                    ) {
                        return '<a href="' . asset($row->payment->voucher_image) . '" target="_blank">
                                    <img src="' . asset($row->payment->voucher_image) . '"
                                        alt="Voucher Image"
                                        class="rounded"
                                        width="50"
                                        height="50"
                                        style="object-fit:cover;border:1px solid #dee2e6;">
                                </a>';
                    }

                    return 'N/A';
                })

                // Student Name
                ->addColumn('student_name', function ($row) {
                    return $row->student?->name ?? 'N/A';
                })
                ->filterColumn('student_name', function ($query, $keyword) {
                    $query->whereHas('student', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

               // Session
                ->addColumn('session_name', function ($row) {
                    return $row->student?->academicSession?->session_name ?? 'N/A';
                })
                ->filterColumn('session_name', function ($query, $keyword) {
                    $query->whereHas('student.academicSession', function ($q) use ($keyword) {
                        $q->where('session_name', 'like', "%{$keyword}%");
                    });
                })

                // Class (via sessionItem)
                ->addColumn('class', function ($row) {
                    return $row->student?->sessionItem?->class ?? 'N/A';
                })
                ->filterColumn('class', function ($query, $keyword) {
                    $query->whereHas('student.sessionItem.class', function ($q) use ($keyword) {
                        $q->where('class', 'like', "%{$keyword}%");
                    });
                })

                // Section (via sessionItem)
                ->addColumn('section', function ($row) {
                    return $row->student?->sessionItem?->section ?? 'N/A';
                })
                ->filterColumn('section', function ($query, $keyword) {
                    $query->whereHas('student.sessionItem.section', function ($q) use ($keyword) {
                        $q->where('section', 'like', "%{$keyword}%");
                    });
                })

                // Month Format
                ->addColumn('month', function ($row) {
                    return FeeChallanHelper::formatChallanMonth($row->month);
                })
                ->filterColumn('month', function ($query, $keyword) {
                    $keyword = trim($keyword);

                    $query->where(function ($q) use ($keyword) {
                        $q->where('month', 'like', "%{$keyword}%");

                        $months = [
                            'jan' => '01', 'january' => '01',
                            'feb' => '02', 'february' => '02',
                            'mar' => '03', 'march' => '03',
                            'apr' => '04', 'april' => '04',
                            'may' => '05',
                            'jun' => '06', 'june' => '06',
                            'jul' => '07', 'july' => '07',
                            'aug' => '08', 'august' => '08',
                            'sep' => '09', 'sept' => '09', 'september' => '09',
                            'oct' => '10', 'october' => '10',
                            'nov' => '11', 'november' => '11',
                            'dec' => '12', 'december' => '12',
                        ];

                        $lower = strtolower($keyword);

                        foreach ($months as $name => $num) {
                            if (str_contains($lower, $name)) {
                                $q->orWhere('month', 'like', "%-{$num}%");
                            }
                        }
                    });
                })

                // Total Amount (Direct Column Search for fast response)
                ->addColumn('amount', function ($row) {
                    return number_format($row->total_amount ?? 0, 0);
                })
                ->filterColumn('amount', function ($query, $keyword) {
                    $keyword = trim($keyword);
                    if ($keyword !== '') {
                        $query->where('total_amount', 'like', "%{$keyword}%");
                    }
                })

                // Challan Type
                ->addColumn('challan_type', function ($row) {
                    if ((int) $row->student?->discount === 1) {
                        return '<span class="badge bg-primary">Discount</span>';
                    }

                    return '<span class="badge bg-secondary">Normal</span>';
                })
                ->filterColumn('challan_type', function ($query, $keyword) {
                    $keyword = strtolower(trim($keyword));

                    if ($keyword === 'discount') {
                        $query->whereHas('student', function ($q) {
                            $q->where('discount', 1);
                        });
                    } elseif ($keyword === 'normal') {
                        $query->whereHas('student', function ($q) {
                            $q->where('discount', 0);
                        })->where('include_admission_fee', false);
                    } elseif (str_contains($keyword, 'admission')) {
                        $query->where('include_admission_fee', true);
                    }
                })

                // Paid Date
                ->addColumn('paid_date', function ($row) {
                    if (!$row->payment || empty($row->payment->paid_date)) {
                        return 'N/A';
                    }

                    return Carbon::parse($row->payment->paid_date)->format('d M Y');
                })
                ->filterColumn('paid_date', function ($query, $keyword) {
                    $keyword = trim($keyword);

                    if ($keyword !== '') {
                        $query->whereHas('payment', function ($q) use ($keyword) {
                            $q->where('paid_date', 'like', "%{$keyword}%");
                        });
                    }
                })

                // Status
                ->addColumn('status', function ($row) {
                    return match ($row->status) {
                        'pending'  => '<span class="badge bg-warning text-dark">Pending</span>',
                        'review'   => '<span class="badge bg-info">Review</span>',
                        'approved', 'paid' => '<span class="badge bg-success">Paid</span>',
                        default    => '<span class="badge bg-secondary">N/A</span>',
                    };
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $keyword = strtolower(trim($keyword));

                    if (in_array($keyword, ['pending', 'review', 'approved', 'paid'])) {
                        $query->where('status', $keyword);
                    }
                })

                // Action
                ->addColumn('action', function ($row) {
                    $printRoute = route('admin.challan.print', $row->id);
                    return '
                        <a href="' . $printRoute . '"
                            class="btn btn-outline-primary btn-sm ms-1 rounded-3"
                            title="Print Challan"
                           target="_blank">
                            <i class="fas fa-print"></i>
                        </a>
                    ';
                })
                ->rawColumns(['voucher_image','session', 'class' , 'section', 'challan_type', 'status', 'action'])
                ->make(true);
        }

        return response()->json([]);
    }

    // Print Form
    public function print(int $id): View
    {
        try{
            $student = Student::with(['parent', 'guardian','sibling','sessionItem'])->findOrFail($id);
            $voucher = null;
            // Discount OFF
            if ((int) $student->discount === 0) {
                $voucher = Voucher::with(['items', 'sessionItem'])
                    ->where('academic_session_id', $student->academic_session_id)
                    ->where('session_item_id', $student->session_item_id)
                    ->first();
            }
            return view('backend.admission.print', compact('student','voucher'));
        }catch (\Exception $e) {
            \Log::error('Print Application Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
      
    }

    // Voucher Print
    public function printVoucher(int $id): View
    {
        try {
            $student = Student::with('sessionItem')->findOrFail($id);
            $voucher = null;
            // Discount OFF
            if ((int) $student->discount === 0) {
                $voucher = Voucher::with(['items', 'sessionItem'])
                    ->where('academic_session_id', $student->academic_session_id)
                    ->where('session_item_id', $student->session_item_id)
                    ->first();
            }
            return view('backend.admission.voucher.voucher-print',compact('student', 'voucher'));
        } catch (\Exception $e) {
            \Log::error('Print Voucher Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // Challan Print
    public function challanPrint(int $challanId): View|RedirectResponse
    {
        try {
            // FIXED: $id ki bajaye $challanId use karein
            $challan = FeeChallan::with(['items', 'student.sessionItem', 'student.parent'])->findOrFail($challanId);
            
            $student = $challan->student;
            $monthLabel = FeeChallanHelper::formatChallanMonth($challan->month);

            return view('backend.admission.challan.challan-print', compact('challan', 'student', 'monthLabel'));

        } catch (\Exception $e) {
            \Log::error('Print Challan Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
    
    // Ajax with Session Id fetch
    public function exitClassSection(Request $request)
    {
        $session_items = SessionItem::where(
            'academic_session_id',
            $request->academic_session_id
        )->get();

        if ($session_items->isEmpty()) {
            return response()->json([
                'status_code' => 404,
                'message' => 'No class/section found.',
                'session_items' => [],
            ]);
        }
        return response()->json([
            'status_code' => 200,
            'session_items' => $session_items,
        ]);
    }

   // Get Voucher Amounts
    public function getVoucherAmounts(Request $request)
    {
        $voucher = Voucher::with('items')
            ->where('academic_session_id', $request->academic_session_id)
            ->where('session_item_id', $request->session_item_id)
            ->first();

        if (!$voucher) {
            $defaultFees = config('fees.default_fees');

            return response()->json([
                'status_code' => 200,
                'voucher_exists' => false,
                'voucher_items' => collect($defaultFees)->map(function ($fee) {
                    return [
                        'fee_name' => $fee,
                        'amount' => 0,
                    ];
                })->values(),
                'total_amount' => 0,
                'due_date' => '',
                'notes' => '',
            ]);
        }
        return response()->json([
            'status_code' => 200,
            'voucher_exists' => true,
            'voucher_items' => $voucher->items,
            'total_amount' => $voucher->total_amount,
            'due_date' => $voucher->due_date
                ? \Carbon\Carbon::parse($voucher->due_date)->format('Y-m-d')
                : '',
            'notes' => $voucher->notes ?? '',
        ]);
    }


    private function createVoucher(array $validated): Voucher
    {
        $feeNames = $validated['fee_names'] ?? [];
        $amounts  = $validated['amounts'] ?? [];

        // Calculate Total Amount
        $totalAmount = array_sum(array_map('floatval', $amounts));

        // Create Main Voucher Record
        $voucher = Voucher::create([
            'academic_session_id' => $validated['academic_session_id'],
            'session_item_id'     => $validated['session_item_id'],
            'due_date'            => $validated['due_date'] ?? now()->toDateString(),
            'total_amount'        => $totalAmount,
            'notes'               => $validated['notes'] ?? null,
        ]);

        // Create Child Voucher Items (Har valid fee_name ki entry hogi, amount 0 ho tab bhi)
        foreach ($feeNames as $index => $feeName) {
            
            // Agar Fee Name hi null ya khali space ho to skip karein
            if (is_null($feeName) || trim($feeName) === '') {
                continue;
            }

            $voucher->items()->create([
                'fee_name' => $feeName,
                'amount'   => (float) ($amounts[$index] ?? 0),
            ]);
        }

        return $voucher;
    }

    // Store Admission Details
    public function submitAdmission(Request $request): RedirectResponse
    {
        $parentExists = Parant::where('father_nic', $request->father_nic)->exists();

        $request->merge([
            'discount' => $request->boolean('discount'),
        ]);

        $validated = $request->validate([
            'name'                        => 'required|string|max:255',
            'date_of_birth'               => 'required|date',
            'gender'                      => 'required|in:Male,Female',
            'b_form_no'                   => 'nullable|string|regex:/^\d{5}-\d{7}-\d$/',
            'religion'                    => 'nullable|string|max:50',
            'caste'                       => 'nullable|string|max:50',
            'domicile'                    => 'nullable|string|max:50',
            'father_name'                 => 'required|string|max:255',
            'father_nic'                  => $parentExists ? 'required|regex:/^\d{5}-\d{7}-\d$/' : 'required|unique:parents,father_nic|regex:/^\d{5}-\d{7}-\d$/',
            'password'                    => [
                $parentExists ? 'nullable' : 'required',
                'string',
                'size:4',
                'regex:/^[A-Za-z0-9]+$/',
            ],
            'contact_no'                  => $parentExists ? 'required|regex:/^03\d{2}-\d{7}$/' : 'required|regex:/^03\d{2}-\d{7}$/|unique:parents,contact_no',
            'mother_name'                 => 'nullable|string|max:255',
            'occupation'                  => 'required|string|max:255',
            'income'                      => 'nullable|numeric|min:0',
            'address'                     => 'required|string',
            'status'                      => 'required|in:active,inactive',
            'guardian_name'               => 'nullable|string|max:255',
            'relation'                    => 'nullable|string|max:50',
            'guardian_nic'                => 'nullable|regex:/^\d{5}-\d{7}-\d$/',
            'guardian_contact_no'         => 'nullable|regex:/^03\d{2}-\d{7}$/|unique:guardians,contact_no',
            'sibling_name'                => 'nullable|string',
            'sibling_class'               => 'nullable|string',
            'sibling_section'             => 'nullable|string',
            'sibling_in_dps'              => 'nullable|integer|min:0',
            'academic_session_id'        => 'required|exists:academic_sessions,id',
            'session_item_id'             => 'required|exists:session_items,id',
            'previous_school'             => 'required|string|max:255',
            'last_fee_paid_upto'          => 'nullable|date',
            'fees_paid_last_institution'  => 'required|in:0,1',
            'games_sports'                => 'nullable|string',
            'extra_curricular'            => 'nullable|string',
            'admission_date'              => 'nullable|date',
            'student_photo'               => 'required|string|min:100',
            'fee_names'                   => 'nullable|array',
            'fee_names.*'                 => 'nullable|string|max:255',
            'amounts'                     => 'nullable|array',
            'amounts.*'                   => 'nullable|numeric|min:0',
            'notes'                       => 'nullable|string',
            'discount'                    => 'required|boolean',
            'discount_amount'             => 'required_if:discount,1|nullable|numeric|min:0',
            'discount_due_date'           => 'required_if:discount,1|nullable|date',
            'discount_notes'              => 'nullable|string',
            'receipt_no'                  => 'nullable|string',
            'fee_paid_date'               => 'nullable|date',
        ]);

        $student = null;
        $challan = null;

        DB::transaction(function () use ($validated, &$student, &$challan) {

            $getParentPortal = Parant::where('father_nic', $validated['father_nic'])->first();

            if (!$getParentPortal) {
                $parent = Parant::create([
                    'father_name' => $validated['father_name'],
                    'father_nic'  => $validated['father_nic'] ?? null,
                    'password'    => Hash::make($validated['password']),
                    'mother_name' => $validated['mother_name'] ?? null,
                    'occupation'  => $validated['occupation'],
                    'income'      => $validated['income'],
                    'address'     => $validated['address'] ?? null,
                    'contact_no'  => $validated['contact_no'],
                ]);
                $parentId = $parent->id;
            } else {
                $parentId = $getParentPortal->id;
            }

            $photoPath = null;
            if (!empty($validated['student_photo']) && str_contains($validated['student_photo'], ';base64,')) {
                $img = explode(";base64,", $validated['student_photo']);
                $ext = explode('/', explode(':', $img[0])[1])[1] ?? 'jpg';
                $filename = 'stu_' . time() . '.' . $ext;

                $uploadPath = public_path('upload/admission');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                file_put_contents(
                    $uploadPath . '/' . $filename,
                    base64_decode($img[1])
                );

                $photoPath = 'upload/admission/' . $filename;
            }

            $student = Student::create([
                'parent_id'                  => $parentId,
                'name'                       => $validated['name'],
                'date_of_birth'              => $validated['date_of_birth'],
                'gender'                     => $validated['gender'],
                'b_form_no'                  => $validated['b_form_no'] ?? null,
                'religion'                   => $validated['religion'] ?? null,
                'caste'                      => $validated['caste'] ?? null,
                'domicile'                   => $validated['domicile'] ?? null,
                'academic_session_id'        => $validated['academic_session_id'] ?? null,
                'session_item_id'            => $validated['session_item_id'] ?? null,
                'discount'                   => $validated['discount'] ?? 0,
                'discount_amount'            => $validated['discount'] ? $validated['discount_amount'] : 0,
                'discount_due_date'          => $validated['discount'] ? $validated['discount_due_date'] : null,
                'discount_notes'             => $validated['discount'] ? $validated['discount_notes'] : null,
                'last_fee_paid_upto'         => $validated['last_fee_paid_upto'] ?? null,
                'fees_paid_last_institution' => $validated['fees_paid_last_institution'],
                'games_sports'               => $validated['games_sports'] ?? null,
                'extra_curricular'           => $validated['extra_curricular'] ?? null,
                'admission_date'             => $validated['admission_date'] ?? null,
                'photo_path'                 => $photoPath,
                'receipt_no'                 => $validated['receipt_no'] ?? null,
                'fee_paid_date'              => $validated['fee_paid_date'] ?? null,
                'status'                     => $validated['status'],
                'created_by'                 => Auth::id()
            ]);

            $student->update([
                'register_no' => 'REG-' . str_pad($student->id, 5, '0', STR_PAD_LEFT)
            ]);

            if (!empty($validated['guardian_name'])) {
                Guardian::create([
                    'student_id'    => $student->id,
                    'guardian_name' => $validated['guardian_name'],
                    'relation'      => $validated['relation'] ?? 'Other',
                    'guardian_nic'  => $validated['guardian_nic'] ?? null,
                    'contact_no'    => $validated['guardian_contact_no'] ?? null
                ]);
            }

            Sibling::create([
                'student_id'      => $student->id,
                'sibling_name'    => $validated['sibling_name'] ?? null,
                'sibling_class'   => $validated['sibling_class'] ?? null,
                'sibling_section' => $validated['sibling_section'] ?? null,
                'sibling_in_dps'  => $validated['sibling_in_dps'] ?? 0,
            ]);

            $month = !empty($validated['admission_date'])
                ? Carbon::parse($validated['admission_date'])->format('Y-m')
                : now()->format('Y-m');

            /* 
            |--------------------------------------------------------------------------
            | VOUCHER LOGIC (Check Exist OR Create with Items)
            |--------------------------------------------------------------------------
            */
           $hasDiscount = isset($validated['discount']) && (int) $validated['discount'] === 1;

            if (!$hasDiscount) {
                $voucher = Voucher::where('academic_session_id', $validated['academic_session_id'])
                    ->where('session_item_id', $validated['session_item_id'])
                    ->first();

                if (!$voucher) {
                    // Agar voucher nahi hai aur student non-discounted hai, toh naya voucher create karo
                    $voucher = $this->createVoucher($validated);
                }
            }

            // Generate First Challan with Snapshot Items
            $challan = FeeChallanHelper::createAdmissionChallan($student, $month);
            
        });

        return redirect()->route('admin.add.admission')
            ->with('success', 'Admission saved successfully!')
            ->with('admission_student_id', $student->id)
            ->with('admission_challan_id', $challan?->id);
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
        $parentId = $student->parent?->id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'b_form_no' => 'nullable|string|regex:/^\d{5}-\d{7}-\d$/',
            'religion' => 'nullable|string|max:50',
            'caste' => 'nullable|string|max:50',
            'domicile' => 'nullable|string|max:50',
            'father_name' => 'required|string|max:255',
            'father_nic' => [
                'required',
                'regex:/^\d{5}-\d{7}-\d$/',
                Rule::unique('parents','father_nic')->ignore($parentId),
            ],
            'password' =>      'nullable','string','size:4','regex:/^[A-Za-z0-9]+$/',
            'mother_name' => 'nullable|string|max:255',
            'occupation' => 'required|string|max:255',
            'income'       => 'nullable|numeric|min:0',
           'contact_no' => [
                'required',
                'regex:/^03\d{2}-\d{7}$/',
                Rule::unique('parents','contact_no')->ignore($parentId),
            ],
            'address' => 'required|string',
            'guardian_name' => 'nullable|string|max:255',
            'relation' => 'nullable|string|max:50',
            'guardian_nic' => 'nullable|regex:/^\d{5}-\d{7}-\d$/',
            'guardian_contact_no' =>  [
                'nullable',
                'regex:/^03\d{2}-\d{7}$/',
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
            'student_photo' => 'nullable|string|min:100',
            'fee_names' => 'nullable|array',
            'fee_names.*' => 'nullable|string|max:255',
            'amounts' => 'nullable|array',
            'amounts.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'discount' => 'required|boolean',
            'discount_amount' => 'required_if:discount,1|nullable|numeric|min:0',
            'discount_due_date' => 'required_if:discount,1|nullable|date',
            'discount_notes' => 'nullable|string',
            'receipt_no' => 'nullable','string',
            Rule::unique('students', 'receipt_no')->ignore($student->id),
            'fee_paid_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ]);
        $validated['discount'] = (int) $validated['discount'];
        $validated['discount_amount'] = $validated['discount'] === 1
            ? (float) ($validated['discount_amount'] ?? 0)
            : 0;
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
            $getParentPortal = Parant::with('students:id,parent_id')->where('father_nic', $validated['father_nic'])->first();
            
            // Update Parent
            if($getParentPortal)
            {
                $getParentPortal->update([
                    'password'    => $password,   
                    'mother_name' => $validated['mother_name'] ?? null, 
                    'income'      => $validated['income'],       
                ]);
                $parentId =  $getParentPortal->id;
            }elseif(!$getParentPortal){
                // new parent create
                $parent = Parant::create([
                    'father_name' => $validated['father_name'],
                    'father_nic'  => $validated['father_nic'] ?? null,
                    'password'    => $password,
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
                'discount' =>  ($validated['discount'] === 1) ? $validated['discount']  : 0,
                'discount_amount' => ($validated['discount'] === 1) ? $validated['discount_amount']  : 0,
                'discount_due_date' =>  ($validated['discount'] === 1) ? $validated['discount_due_date']  : null,
                'discount_notes' => ($validated['discount'] === 1) ? $validated['discount_notes']  : null,
                'last_fee_paid_upto' => $validated['last_fee_paid_upto'] ?? null,
                'fees_paid_last_institution' => $validated['fees_paid_last_institution'],
                'games_sports' => $validated['games_sports'] ?? null,
                'extra_curricular' => $validated['extra_curricular'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'photo_path' => $photoPath,
                'receipt_no' => $validated['receipt_no'],
                'fee_paid_date'       => $validated['fee_paid_date'] ?? null,
                'status'              => $validated['status'],
                'created_by'=>Auth::id()
            ]);
            // Update Guardian
                if ($guardian) {
                    if (!empty($validated['guardian_name'])) {
                        $guardian->update([
                            'guardian_name' => $validated['guardian_name'],
                            'relation' => $validated['relation'] ?? 'Other',
                            'guardian_nic' => $validated['guardian_nic'] ?? null,
                            'contact_no' => $validated['guardian_contact_no'] ?? null,
                        ]);
                    } else {
                        $guardian->delete();
                    }
                } elseif (!empty($validated['guardian_name'])) {
                    $student->guardian()->create([
                        'guardian_name' => $validated['guardian_name'],
                        'relation' => $validated['relation'] ?? 'Other',
                        'guardian_nic' => $validated['guardian_nic'] ?? null,
                        'contact_no' => $validated['guardian_contact_no'] ?? null,
                    ]);
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
            $voucherExists = Voucher::where('academic_session_id', $validated['academic_session_id'])
                ->where('session_item_id', $validated['session_item_id'])
                ->first();
            if ($validated['discount'] === 0) {
                if (!$voucherExists) {
                    $this->createVoucher($validated);
                }
            }
        });
        //  Existing challan if exit
        $latestChallan = FeeChallan::where('student_id', $student->id)
            ->latest()
            ->first();
       return redirect()
                ->route('admin.admission.index')
                ->with('success', 'Admission updated successfully!')
                ->with('admission_student_id', $student->id)
                ->with('admission_challan_id', $latestChallan?->id);
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
