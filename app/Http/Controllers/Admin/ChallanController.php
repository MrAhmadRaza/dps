<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Helpers\FeeChallanHelper;
use App\Services\AdminPortal\ChallanStatus;
use App\Services\OneLinkBillService;
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

    // All challans (page + DataTables JSON). ?status= one of ChallanStatus::FILTERS
    public function index(Request $request, ChallanStatus $statuses): View|JsonResponse
    {
        $pageTitle = 'Challans';
        $filter = array_key_exists($request->query('status'), ChallanStatus::FILTERS) ? $request->query('status') : 'all';

        if ($request->ajax()) {
            $query = FeeChallan::with([
                'student.academicSession',
                'student.sessionItem',
                'payment',
            ])->select('fee_challans.*');

            $statuses->applyFilter($query, $filter);

            // Review queue reads best oldest-first; everything else newest-first.
            $filter === 'review' ? $query->oldest('fee_challans.updated_at') : $query->latest('fee_challans.id');

            return datatables()->of($query)
                ->addIndexColumn()

                ->addColumn('student_name', function ($row) {
                    $name = e($row->student?->name ?? 'N/A');
                    $reg  = $row->student?->register_no ? '<div class="small text-muted">' . e($row->student->register_no) . '</div>' : '';
                    return '<div class="name-cell">' . $name . '</div>' . $reg;
                })
                ->filterColumn('student_name', function ($query, $keyword) {
                    $query->whereHas('student', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")->orWhere('register_no', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('voucher_image', function (FeeChallan $row) {
                    $img = $row->payment?->voucher_image;
                    if ($img && is_file(public_path($img))) {
                        $url = e(asset($img));
                        return '<a href="' . $url . '" data-zoom="' . $url . '" aria-label="Open payment proof"><img src="' . $url . '" alt="" class="proof-mini" loading="lazy"></a>';
                    }
                    return '<span class="text-muted">—</span>';
                })

                ->addColumn('academic_session_id', function ($row) {
                    return e($row->student->academicSession->session_name ?? '—');
                })
                ->filterColumn('academic_session_id', function ($query, $keyword) {
                    $query->whereHas('student.academicSession', function ($q) use ($keyword) {
                        $q->where('session_name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('class', function ($row) {
                    $item = $row->student?->sessionItem;
                    return $item ? e($item->class . '-' . $item->section) : '—';
                })
                ->filterColumn('class', function ($query, $keyword) {
                    $query->whereHas('student.sessionItem', function ($q) use ($keyword) {
                        $q->where('class', 'like', "%{$keyword}%")->orWhere('section', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('month', function ($row) {
                    $label = e(FeeChallanHelper::formatChallanMonth($row->month));
                    $no = $row->challan_no ? '<div class="small text-muted mono">#' . e($row->challan_no) . '</div>' : '';
                    return '<div class="fw-650 text-ink">' . $label . '</div>' . $no;
                })
                ->filterColumn('month', function ($query, $keyword) {
                    $keyword = trim($keyword);

                    $query->where(function ($q) use ($keyword) {
                        $q->where('month', 'like', "%{$keyword}%")->orWhere('challan_no', 'like', "%{$keyword}%");

                        $months = [
                            'jan' => '01', 'january' => '01', 'feb' => '02', 'february' => '02',
                            'mar' => '03', 'march' => '03', 'apr' => '04', 'april' => '04', 'may' => '05',
                            'jun' => '06', 'june' => '06', 'jul' => '07', 'july' => '07',
                            'aug' => '08', 'august' => '08', 'sep' => '09', 'sept' => '09', 'september' => '09',
                            'oct' => '10', 'october' => '10', 'nov' => '11', 'november' => '11',
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
                    $due = $row->due_date ? '<div class="small text-muted">Due ' . e(Carbon::parse($row->due_date)->format('j M')) . '</div>' : '';
                    return '<div class="fw-650 text-ink num">' . number_format((float) ($row->total_amount ?? 0), 0) . '</div>' . $due;
                })

                ->addColumn('challan_type', function ($row) {
                    return (int) $row->student?->discount === 1
                        ? '<span class="chip chip-brand no-dot">Concession</span>'
                        : '<span class="text-muted">Normal</span>';
                })
                ->filterColumn('challan_type', function ($query, $keyword) {
                    $k = strtolower($keyword);
                    if (in_array($k, ['discount', 'concession'], true)) {
                        $query->whereHas('student', fn ($q) => $q->where('discount', 1));
                    }
                    if ($k === 'normal') {
                        $query->whereHas('student', fn ($q) => $q->where('discount', 0));
                    }
                })

                ->addColumn('paid_date', function ($row) {
                    if (! $row->payment?->paid_date) {
                        return '<span class="text-muted">—</span>';
                    }
                    $bank = $row->payment->bank_name ? '<div class="small text-muted text-truncate" style="max-width:140px">' . e($row->payment->bank_name) . '</div>' : '';
                    return e(Carbon::parse($row->payment->paid_date)->format('j M Y')) . $bank;
                })

                ->addColumn('status', fn ($row) => $statuses->chip($row))
                ->filterColumn('status', function ($query, $keyword) {
                    $keyword = strtolower(trim($keyword));
                    if (in_array($keyword, ['pending', 'review', 'approved', 'expired'])) {
                        $query->where('status', $keyword);
                    }
                })

                ->addColumn('action', function ($row) {
                    return view('admin.partials.challan-action', [
                        'challan'        => $row,
                        'status'         => $row->status,
                        'viewRoute'      => route('admin.challan.view', $row->id),
                        'approvedRoute'  => route('admin.challan.approved', $row->id),
                        'pendingRoute'   => route('admin.challan.pending', $row->id),
                        'deleteRoute'    => route('admin.challan.destroy', $row->id),
                        'paidViaOneLink' => $row->payment?->source === 'onelink',
                    ])->render();
                })

                ->rawColumns(['student_name', 'month', 'amount', 'paid_date', 'status', 'challan_type', 'voucher_image', 'action'])
                ->make(true);
        }

        $students = Student::with('sessionItem')->where('status', 'active')->orderBy('name')->get(['id', 'name', 'register_no', 'session_item_id']);
        $counts = $statuses->counts();

        return view('admin.challans.show-challan', compact('pageTitle', 'students', 'filter', 'counts'));
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

    // Approve (mark as paid). POST only.
    public function approvedChallan(int $id): RedirectResponse
    {
        try {
            $challan = FeeChallan::with('student')->findOrFail($id);
            $challan->update(['status' => 'approved']);

            return back()->with('success', ($challan->student->name ?? 'The') . "'s " . FeeChallanHelper::formatChallanMonth($challan->month) . ' challan is marked as paid.');
        } catch (\Exception $e) {
            \Log::error('Approved Challan error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. The challan was not changed.');
        }
    }

    // Set back to pending (unpaid). POST only. Not allowed for 1LINK-paid challans.
    public function pendingChallan(int $id): RedirectResponse
    {
        try {
            $challan = FeeChallan::with(['payment', 'student'])->findOrFail($id);
            if ($challan->isPaidViaOneLink()) {
                return back()->with('error', 'This challan was paid through 1LINK and cannot be set back to unpaid.');
            }
            $challan->update(['status' => 'pending']);

            return back()->with('success', ($challan->student->name ?? 'The') . "'s " . FeeChallanHelper::formatChallanMonth($challan->month) . ' challan is set back to unpaid.');
        } catch (\Exception $e) {
            \Log::error('Pending Challan error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. The challan was not changed.');
        }
    }

    // View Challan
    public function viewChallan(int $id, ChallanStatus $statuses, OneLinkBillService $bills): View|RedirectResponse
    {
        $challan = FeeChallan::with([
            'student.academicSession',
            'student.sessionItem',
            'student.parent',
            'payment',
            'items',
        ])->find($id);

        if (! $challan) {
            return redirect()->route('admin.challan.index')->with('error', 'That challan was not found. It may have been deleted.');
        }

        $pageTitle = 'Challan ' . ($challan->challan_no ?: '#' . $challan->id);
        $status    = $statuses->for($challan);
        $amounts   = $bills->amountsFor($challan);

        return view('admin.challans.view-challan', compact('challan', 'pageTitle', 'status', 'amounts'));
    }

    // Destroy Challan
    public function destroyChallan(int $id): RedirectResponse
    {
        try {
            $challan = FeeChallan::with('payment')->findOrFail($id);
            if ($challan->isPaidViaOneLink()) {
                return back()->with('error', 'This challan was paid through 1LINK and cannot be deleted.');
            }
            $challan->delete();

            // Deleted from its own page: go to the list instead of back to a missing page.
            $previous = url()->previous();
            $redirect = str_contains($previous, '/challans/view-chalan/')
                ? redirect()->route('admin.challan.index')
                : back();

            return $redirect->with('success', 'Challan deleted.');
        } catch (\Exception $e) {
            \Log::error('Destroy Challan error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. The challan was not deleted.');
        }
    }
}
