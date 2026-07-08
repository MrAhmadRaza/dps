<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use App\Models\Voucher;
use App\Models\AcademicSession;
use App\Models\VoucherItem;
use Carbon\Carbon;
use DataTables;
use Illuminate\Support\Str;


class VoucherController extends Controller
{

    // Show Voucher
    public function index(Request $request): View|JsonResponse
    {
        $pageTitle = 'Voucher';

        if ($request->ajax()) {
            $query = Voucher::query();
            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('academic_session_id', function (Voucher $voucher) {
                    return $voucher->academicSession->session_name ?? 'N/A';
                })

                 ->addColumn('due_date', function (Voucher $voucher) {
                     return Carbon::parse($voucher->due_date)
                        ->format('d M Y');
                })

                ->addColumn('created_at', function (Voucher $voucher) {
                    return Carbon::parse($voucher->created_at)
                        ->format('d M Y');
                })
                ->addColumn('action', function (Voucher $voucher) {
                    $printRoute  = route('admin.voucher.print', $voucher->id);
                    $viewRoute = route('admin.voucher.view', $voucher->id);
                    $editRoute = route('admin.voucher.edit', $voucher->id);
                    $deleteRoute = route('admin.voucher.destroy', $voucher->id);
                    return  view('backend.partials.voucher-action',compact('printRoute','viewRoute','editRoute', 'deleteRoute'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.voucher.show-voucher', compact('pageTitle'));
    }

    public function printVoucher(int $id)
    {
        $voucher = Voucher::with('academicSession','sessionItem','items')->findOrFail($id);
        return view('backend.voucher.voucher-print', compact('voucher'));
    }
    
    // Add Voucher
    public function addVoucher(): View
    {
        $pageTitle = 'Add Voucher';
        $defaultFees = [
            'Admission / Re Admission Fee',
            'Tuition Fee',
            'Arrears / Installment',
            'Late Fee / Absent Fee',
            'Sports Fund',
            'Library / Magazine Fee',
            'IT Lab / Science Lab Fee',
            'Stationery / Exam Fee',
            'Miscellaneous / Prospectus'
        ];
        $students = Student::select('id','name')->get();
        $sessions = AcademicSession::select('id','session_name')->get();
        return view('backend.voucher.add-voucher', compact('students','sessions','defaultFees','pageTitle'));
    }

    // View Voucher
    public function viewVoucher(int $id): View
    {
        $pageTitle = 'Voucher Details';
        $voucher = Voucher::with('academicSession','sessionItem','items')->findOrFail($id);
        return view('backend.voucher.view-voucher', compact('voucher','pageTitle'));
    }

    public function storeVoucher(Request $request): RedirectResponse|View
    {
        $request->validate([
            'academic_session_id'  => 'required|exists:academic_sessions,id',
            'session_item_id'      => 'required|exists:session_items,id',
            'due_date'             => 'required|date',
            'notes'                => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            $totalAmount = 0;
            if($request->amounts){
                foreach($request->amounts as $amount){
                    $totalAmount += $amount ?? 0;
                }
            }
            // Vocuher Create
            $voucher = Voucher::create([
                'academic_session_id' => $request->academic_session_id,
                'session_item_id'     => $request->session_item_id,
                'due_date'            => $request->due_date,
                'total_amount'        => $totalAmount,
                'notes'               => $request->notes,
            ]);

            // Fee Items Create
            if($request->fee_names)
            {
                foreach($request->fee_names as $key => $name)
                {
                    VoucherItem::create([
                        'voucher_id' => $voucher->id,
                        'fee_name' => $name,
                        'amount' => $request->amounts[$key] ?? 0
                    ]);
                }
            }
        
            DB::commit();

            return redirect()
                ->route('admin.voucher.add')
                ->with('success', 'Voucher created successfully.');

        } catch (\Exception $e) {

            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    // Edit Fee Item
    public function editVoucher(int $id): View
    {
        $pageTitle = 'Edit Voucher';
        $vouchers = Voucher::with('academicSession','sessionItem','items')->findOrFail($id);
        $sessions = AcademicSession::all();
        return view('backend.voucher.edit-voucher', compact(
            'vouchers',
            'sessions',
            'pageTitle'
        ));
    }

    // Update Voucher
    public function updateVoucher(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'academic_session_id'  => 'required|exists:academic_sessions,id',
            'session_item_id'      => 'required|exists:session_items,id',
            'due_date'             => 'required|date',
            'notes'                => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $voucher = Voucher::findOrFail($id);
            // Calculate Total Automatically
            $totalAmount = 0;
            if($request->amounts){
                foreach($request->amounts as $amount){
                    $totalAmount += $amount ?? 0;
                }
            }
            // Update Voucher
            $voucher->update([
                'academic_session_id' => $request->academic_session_id,
                'session_item_id'     => $request->session_item_id,
                'due_date' => $request->due_date,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);
            // Delete Old Items
            $voucher->items()->delete();
            // Insert New Items
            if($request->fee_names){
                foreach($request->fee_names as $key => $name){
                    $voucher->items()->create([
                        'fee_name' => $name,
                        'amount'   => $request->amounts[$key] ?? 0
                    ]);
                }
            }
            DB::commit();
            return redirect()
                ->route('admin.voucher.index')
                ->with('success', 'Voucher Updated Successfully!');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Something went wrong!');
        }
    }

    // Delete Voucher
    public function destroyVoucher(int $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $voucher = Voucher::with('items')->findOrFail($id);
            // Then delete voucher
            $voucher->delete();
            DB::commit();
            return redirect()
                ->route('admin.voucher.index')
                ->with('success', 'Voucher deleted successfully!');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Something went wrong!');
        }
    }
}
