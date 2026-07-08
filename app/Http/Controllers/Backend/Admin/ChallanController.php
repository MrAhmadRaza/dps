<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Parant;
use App\Models\Voucher;
use App\Models\FeeChallan;
use Carbon\Carbon;
use DataTables;
use Illuminate\Support\Facades\Auth;

class ChallanController extends Controller
{
    // All Challans
   public function index(Request $request): View|JsonResponse
    {
        $pageTitle = 'Fee Challans';
        if ($request->ajax()) {
            // 🔹 Fetch all challans with related student, voucher, and payment
            $query = FeeChallan::with([
                'voucher',
                'student.academicSession',
                'student.sessionItem',
                'payment'
            ])->latest();
            return datatables()->of($query)
                // Index column
                ->addIndexColumn()

                ->addColumn('voucher_image', function (FeeChallan $row) {
                    // Check if payment exists and voucher_image file exists
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

                // Session Name
                ->addColumn('academic_session_id', function ($row) {
                    return $row->student->academicSession->session_name ?? 'N/A';
                })

                // Class
                ->addColumn('class', function ($row) {
                    return $row->student->sessionItem->class ?? 'N/A';
                })

                 // Secion
                ->addColumn('section', function ($row) {
                    return $row->student->sessionItem->section ?? 'N/A';
                })

                // Student Name
                ->addColumn('student_name', function ($row) {
                    return $row->student->name ?? 'N/A';
                })
              
                // Voucher Amount
                ->addColumn('amount', function ($row) {
                    return $row->voucher->total_amount ?? 0;
                })
                // Payment Status
                ->addColumn('payment_status', function ($row) {
                    if (!$row->payment) {
                        return '<span class="badge bg-danger">Unpaid</span>';
                    }

                    return '<span class="badge bg-success">Paid</span>';
                })

                // Payment Date
                ->addColumn('paid_date', function ($row) {
                    return $row->payment?->paid_date
                        ? Carbon::parse($row->payment->paid_date)->format('d M Y')
                        : 'N/A';
                })

                // Challan Status (Pending / Review / Approved)
                ->addColumn('payment_status', function ($row) {
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

                // Action Buttons (View / Edit / Delete)
                ->addColumn('action', function ($row) {
                    $viewRoute = route('admin.challan.view', $row->id);
                    $approvedRoute = route('admin.challan.approved', $row->id);
                    $pendingRoute = route('admin.challan.pending', $row->id);
                    $deleteRoute = route('admin.challan.destroy', $row->id);
                    $status = $row->status;
                    return view('backend.partials.challan-action', compact(
                        'viewRoute','status','approvedRoute','pendingRoute','deleteRoute'
                    ))->render();
                })
                ->rawColumns(['payment_status', 'voucher_image', 'action'])
                ->make(true);
        }
        return view('backend.challans.show-challan', compact('pageTitle'));
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
        try{
            $challan = FeeChallan::with([
                'voucher',
                'student.academicSession',
                'student.sessionItem',
                'payment'
            ])->find($id);
            $pageTitle = 'View Challan';
            return view('backend.challans.view-challan',compact('challan','pageTitle'));
        }catch (\Exception $e) {
            \Log::error('View Challan error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
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
