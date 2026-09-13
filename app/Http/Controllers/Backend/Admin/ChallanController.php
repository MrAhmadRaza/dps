<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Helpers\FeeChallanHelper;
use App\Models\Student;
use App\Models\Parant;
use App\Models\Voucher;
use App\Models\FeeChallan;
use Carbon\Carbon;
use DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChallanController extends Controller
{  

    // All Challans
    public function index(Request $request): View|JsonResponse
    {
        $pageTitle = 'Fee Challans';
        $students = Student::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        if ($request->ajax()) {

            $query = FeeChallan::with([
                'items',
                'student.academicSession',
                'student.sessionItem',
                'payment'
            ])->latest();

            return datatables()->of($query)
                ->addIndexColumn()

                ->addColumn('student_name', function ($row) {
                    return $row->student?->name ?? 'N/A';
                })
                ->filterColumn('student_name', function ($query, $keyword) {
                    $query->whereHas('student', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('voucher_image', function (FeeChallan $row) {
                    if ($row->payment && $row->payment->voucher_image && file_exists(public_path($row->payment->voucher_image))) {
                        return '<a href="' . asset($row->payment->voucher_image) . '" target="_blank">
                                    <img src="' . asset($row->payment->voucher_image) . '"
                                        alt="Voucher Image"
                                        class="rounded"
                                        width="50"
                                        height="50"
                                        style="object-fit: cover; border: 1px solid #dee2e6;">
                                </a>';
                    }
                    return 'N/A';
                })

                ->addColumn('academic_session_id', function ($row) {
                    return $row->student->academicSession->session_name ?? 'N/A';
                })
                ->filterColumn('academic_session_id', function ($query, $keyword) {
                    $query->whereHas('student.academicSession', function ($q) use ($keyword) {
                        $q->where('session_name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('class', function ($row) {
                    return $row->student->sessionItem->class ?? 'N/A';
                })
                ->filterColumn('class', function ($query, $keyword) {
                    $query->whereHas('student.sessionItem', function ($q) use ($keyword) {
                        $q->where('class', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('section', function ($row) {
                    return $row->student->sessionItem->section ?? 'N/A';
                })
                ->filterColumn('section', function ($query, $keyword) {
                    $query->whereHas('student.sessionItem', function ($q) use ($keyword) {
                        $q->where('section', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('month', function ($row) {
                    $month = $row->month ?? 'N/A';

                    if (str_contains($month, ' to ')) {
                        try {
                            [$start, $end] = explode(' to ', $month);
                            $startFormatted = Carbon::createFromFormat('Y-m', trim($start))->format('M Y');
                            $endFormatted   = Carbon::createFromFormat('Y-m', trim($end))->format('M Y');
                            return $startFormatted . ' to ' . $endFormatted;
                        } catch (\Exception $e) {
                            return $month;
                        }
                    }

                    try {
                        return Carbon::createFromFormat('Y-m', $month)->format('M Y');
                    } catch (\Exception $e) {
                        return $month;
                    }
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

                ->addColumn('amount', function ($row) {
                    return number_format((float) ($row->total_amount ?? 0), 0);
                })

                ->addColumn('challan_type', function ($row) {
                    if ((int) $row->student?->discount === 1) {
                        return '<span class="badge bg-primary">Discount</span>';
                    }
                    return '<span class="badge bg-secondary">Normal</span>';
                })
                ->filterColumn('challan_type', function ($query, $keyword) {
                    if (strtolower($keyword) === 'discount') {
                        $query->whereHas('student', function ($q) {
                            $q->where('discount', 1);
                        });
                    }

                    if (strtolower($keyword) === 'normal') {
                        $query->whereHas('student', function ($q) {
                            $q->where('discount', 0);
                        });
                    }
                })

                ->addColumn('paid_date', function ($row) {
                    return $row->payment?->paid_date
                        ? Carbon::parse($row->payment->paid_date)->format('d M Y')
                        : 'N/A';
                })

                ->addColumn('status', function ($row) {
                    switch ($row->status) {
                        case 'pending':
                            return '<span class="badge bg-warning">Pending</span>';
                        case 'review':
                            return '<span class="badge bg-info">Review</span>';
                        case 'approved':
                            return '<span class="badge bg-success">Approved</span>';
                        default:
                            return '<span class="badge bg-secondary">N/A</span>';
                    }
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $keyword = strtolower(trim($keyword));

                    if (in_array($keyword, ['pending', 'review', 'approved'])) {
                        $query->where('status', $keyword);
                    }
                })

                ->addColumn('action', function ($row) {
                    $viewRoute = route('admin.challan.view', $row->id);
                    $approvedRoute = route('admin.challan.approved', $row->id);
                    $pendingRoute = route('admin.challan.pending', $row->id);
                    $deleteRoute = route('admin.challan.destroy', $row->id);
                    $status = $row->status;

                    return view('backend.partials.challan-action', compact(
                        'viewRoute', 'status', 'approvedRoute', 'pendingRoute', 'deleteRoute'
                    ))->render();
                })

                ->rawColumns(['status', 'challan_type', 'voucher_image', 'action'])
                ->make(true);
        }

        return view('backend.challans.show-challan', compact('pageTitle', 'students'));
    }

    // Custom Challan 
    public function customGenerate(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id'  => ['required', 'exists:students,id'],
            'start_month' => ['required', 'date_format:Y-m'],
            'end_month'   => ['required', 'date_format:Y-m'],
        ]);

        try {
            $student = Student::where('status', 'active')
                ->whereNotNull('academic_session_id')
                ->whereNotNull('session_item_id')
                ->findOrFail($request->student_id);

            $startMonth = Carbon::createFromFormat('Y-m', $request->start_month)->startOfMonth();
            $endMonth   = Carbon::createFromFormat('Y-m', $request->end_month)->startOfMonth();

            if ($startMonth->gt($endMonth)) {
                return back()
                    ->withInput()
                    ->with('error', 'End month must be after or equal to start month.');
            }

            $monthsCount = $startMonth->diffInMonths($endMonth) + 1;
            $monthRange  = $startMonth->format('Y-m') . ' to ' . $endMonth->format('Y-m');

            // Helper call to create custom range challan
            $challan = FeeChallanHelper::createCustomChallan(
                $student,
                $request->start_month,
                $request->end_month
            );

            if (!$challan) {
                return back()
                    ->withInput()
                    ->with('error', 'No voucher or fee structure found for this student class and session.');
            }

            return redirect()
                ->route('admin.challan.index')
                ->with('success', "Custom challan generated successfully for {$monthsCount} month(s) ({$monthRange}).");

        } catch (\Exception $e) {
            \Log::error('Custom Challan Generate Error: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while generating custom challan.');
        }
    }

    // Approved Challan
    public function approvedChallan(int $id): RedirectResponse
    {
        try{
           $challan =  FeeChallan::findOrFail($id);
           $challan->update(['status' => 'approved']);
           return redirect()->back()->with('success','Challan Approved Successfully');
       }catch (\Exception $e) {
            \Log::error('Approved Challan error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // Pending Challan
    public function pendingChallan(int $id): RedirectResponse
    {
        try{
            $challan =  FeeChallan::findOrFail($id);
            $challan->update(['status' => 'pending']);
            return redirect()->back()->with('success','Challan Pending Successfully');
        }catch (\Exception $e) {
            \Log::error('Pending Challan error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // View Challan 
    public function viewChallan(int $id): View
    {
        try {
            $challan = FeeChallan::with([
                'student.academicSession',
                'student.sessionItem',
                'payment'
            ])->findOrFail($id);

            $pageTitle = 'View Challan';
            return view('backend.challans.view-challan', compact('challan', 'pageTitle'));
        } catch (\Exception $e) {
            \Log::error('View Challan error: ' . $e->getMessage());
            return back()->with('error', 'Challan record not found or something went wrong');
        }
    }

    // Destroy Challan
    public function destroyChallan(int $id): RedirectResponse
    {
        try{
            $challan =  FeeChallan::findOrFail($id);
            $challan->delete();
            return redirect()->back()->with('success','Challan Delete Successfully');
        }catch (\Exception $e) {
            \Log::error('View Challan error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

}
