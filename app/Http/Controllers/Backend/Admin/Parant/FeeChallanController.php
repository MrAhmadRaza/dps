<?php

namespace App\Http\Controllers\Backend\Admin\Parant;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use App\Helpers\FeeChallanHelper;
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
   // 1. Fee Challans Index Page & DataTables Response
    public function index(Request $request): View|JsonResponse
    {
        $pageTitle = 'Fee Challans';
        $parentId  = auth()->guard('parents')->id();

        $students = Student::where('parent_id', $parentId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($request->ajax()) {
            $query = FeeChallan::with([
                    'items', // Voucher ki jagah direct items relation
                    'student.academicSession',
                    'student.sessionItem',
                    'payment'
                ])
                ->whereHas('student', function ($q) use ($parentId) {
                    $q->where('parent_id', $parentId)
                        ->where('status', 'active');
                })
                ->latest();

            // Specific Student Filter
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            } else {
                $query->whereRaw('1 = 0');
            }

            return datatables()->of($query)
                ->addIndexColumn()

                // Student Name
                ->addColumn('student_name', function ($row) {
                    return $row->student?->name ?? 'N/A';
                })
                ->filterColumn('student_name', function ($query, $keyword) {
                    $query->whereHas('student', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                // Voucher/Payment Slip Image
                ->addColumn('voucher_image', function ($row) {
                    if ($row->payment && $row->payment->voucher_image && file_exists(public_path($row->payment->voucher_image))) {
                        return '<a href="' . asset($row->payment->voucher_image) . '" target="_blank">
                                    <img src="' . asset($row->payment->voucher_image) . '"
                                        class="rounded" width="50" height="50"
                                        style="object-fit:cover;border:1px solid #dee2e6;">
                                </a>';
                    }
                    return 'N/A';
                })

                // Month Formatted
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

                // Direct DB Amount Column
                ->addColumn('amount', function ($row) {
                    return number_format($row->total_amount ?? 0, 0);
                })

                // Type
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
                    }
                    if ($keyword === 'normal') {
                        $query->whereHas('student', function ($q) {
                            $q->where('discount', 0);
                        });
                    }
                })

                // Paid Date
                ->addColumn('paid_date', function ($row) {
                    return $row->payment?->paid_date
                        ? \Carbon\Carbon::parse($row->payment->paid_date)->format('d M Y')
                        : 'N/A';
                })

                // Status
                ->addColumn('status', function ($row) {
                    return match ($row->status) {
                        'pending'  => '<span class="badge bg-warning">Pending</span>',
                        'review'   => '<span class="badge bg-info">Review</span>',
                        'approved' => '<span class="badge bg-success">Approved</span>',
                        default    => '<span class="badge bg-secondary">N/A</span>',
                    };
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $keyword = strtolower(trim($keyword));
                    if (in_array($keyword, ['pending', 'review', 'approved'])) {
                        $query->where('status', $keyword);
                    }
                })

                // Action Buttons
                ->addColumn('action', function ($row) {
                    $printUrl = route('parent.print.challan', $row->id);
                    $viewUrl  = route('parent.view.challan', $row->id);

                    $html = '<a href="' . $printUrl . '" target="_blank" class="text-primary me-2 fs-5" title="Print">
                                <i class="fas fa-print"></i>
                            </a>';

                    if ($row->status === 'pending') {
                        $html .= '<a href="javascript:void(0)" onclick="openUploadModal(' . $row->id . ')" 
                                    class="text-warning me-2 fs-5" title="Upload Voucher">
                                    <i class="fas fa-upload"></i>
                                </a>';
                    }

                    if ($row->status === 'approved') {
                        $html .= '<a href="' . $viewUrl . '" class="text-primary me-2 fs-5" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>';
                    }

                    return $html;
                })

                ->rawColumns(['voucher_image', 'challan_type', 'status', 'action'])
                ->make(true);
        }

        return view('backend.parent.fee-challan.show-Challan', compact('pageTitle', 'students'));
    }

    // 2. Get Specific Student Challans API (JSON)
    public function getChallans(Request $request): JsonResponse
    {
        $parentId = auth()->guard('parents')->id();

        $data = FeeChallan::with([
                'items', 
                'student.academicSession',
                'student.sessionItem',
                'payment'
            ])
            ->whereHas('student', function ($query) use ($parentId) {
                $query->where('parent_id', $parentId)
                    ->where('status', 'active');
            })
            ->where('student_id', $request->student_id)
            ->latest()
            ->get()
            ->map(function ($challan) {
                $challan->formatted_amount = number_format($challan->amount ?? 0, 0);
                $challan->formatted_month  = FeeChallanHelper::formatChallanMonth($challan->month);
                $challan->challan_type     = ((int) $challan->student?->discount === 1) ? 'Discount' : 'Normal';
                $challan->paid_date        = $challan->payment?->paid_date
                    ? \Carbon\Carbon::parse($challan->payment->paid_date)->format('d M Y')
                    : null;

                return $challan;
            });

        return response()->json($data);
    }

    // Fee Challan Print
    public function printChallan(int $id): View
    {
        $parentId = auth()->guard('parents')->id();
        $challan = FeeChallan::with([
                'items',
                'student.parent',
                'student.academicSession',
                'student.sessionItem',
            ])
            ->where('id', $id)
            ->whereHas('student', function ($query) use ($parentId) {
                $query->where('parent_id', $parentId)
                    ->where('status', 'active');
            })
            ->firstOrFail();
        $student = $challan->student;
        $monthLabel = FeeChallanHelper::formatChallanMonth($challan->month);
        $breakdown  = FeeChallanHelper::getPrintBreakdown($challan);
        return view(
            'backend.parent.fee-challan.challan-print',
            compact('challan', 'student', 'monthLabel', 'breakdown')
        );
    }
}
