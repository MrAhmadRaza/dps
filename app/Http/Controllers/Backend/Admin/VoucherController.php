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
use App\Models\SessionItem;
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
            $query = Voucher::query()->orderBy('id', 'desc');
            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('academic_session_id', function (Voucher $voucher) {
                    return $voucher->academicSession->session_name ?? 'N/A';
                })

                ->filterColumn('academic_session_id', function ($query, $keyword) {
                    $query->whereHas('academicSession', function ($q) use ($keyword) {
                        $q->where('session_name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('class', function (Voucher $voucher) {
                    return $voucher->sessionItem->class ?? 'N/A';
                })

                ->filterColumn('class', function ($query, $keyword) {
                    $query->whereHas('sessionItem', function ($q) use ($keyword) {
                        $q->where('class', 'like', "%{$keyword}%");
                    });
                })

                 ->addColumn('section', function (Voucher $voucher) {
                    return $voucher->sessionItem->section ?? 'N/A';
                })

                ->filterColumn('section', function ($query, $keyword) {
                    $query->whereHas('sessionItem', function ($q) use ($keyword) {
                        $q->where('section', 'like', "%{$keyword}%");
                    });
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
        $defaultFees = config('fees.default_fees');
        $students = Student::select('id','name')->get();
        $sessions = AcademicSession::select('id','session_name')->get();
        return view('backend.voucher.add-voucher', compact('students','sessions','defaultFees','pageTitle'));
    }

    public function exitClassSection(Request $request)
    {
        $request->validate([
            'academic_session_id' => 'required|integer',
        ]);

        $sessionItems = SessionItem::where(
            'academic_session_id',
            $request->academic_session_id
        )->get();

        if ($sessionItems->isEmpty()) {
            return response()->json([
                'status_code' => 404,
                'message' => 'No class/section found.',
                'session_items' => [],
            ]);
        }

        return response()->json([
            'status_code' => 200,
            'session_items' => $sessionItems,
        ]);
    }

    public function getVoucherAmounts(Request $request)
    {
        $request->validate([
            'academic_session_id' => 'required|integer',
            'session_item_id' => 'required|integer',
        ]);

        $voucher = Voucher::with('items')
            ->where('academic_session_id', $request->academic_session_id)
            ->where('session_item_id', $request->session_item_id)
            ->first();

        if (!$voucher) {
            return response()->json([
                'status_code' => 200,
                'voucher_exists' => false,
                'voucher_items' => [],
                'total_amount' => 0,
                'due_date' => null,
                'notes' => '',
            ]);
        }

        return response()->json([
            'status_code' => 200,
            'voucher_exists' => true,
            'voucher_items' => $voucher->items,
            'total_amount' => $voucher->total_amount,
            'due_date' => $voucher->due_date?->format('Y-m-d'),
            'notes' => $voucher->notes ?? '',
         
        ]);
    }
    

    // View Voucher
    public function viewVoucher(int $id): View
    {
        $pageTitle = 'Voucher Details';
        $voucher = Voucher::with('academicSession','sessionItem','items')->findOrFail($id);
        return view('backend.voucher.view-voucher', compact('voucher','pageTitle'));
    }

    // Store Voucher
    public function storeVoucher(Request $request): RedirectResponse|View
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'session_item_id'     => 'required|exists:session_items,id',
            'due_date'            => 'required|date',
            'notes'               => 'nullable|string',
            'fee_names'           => 'nullable|array',
            'amounts'             => 'nullable|array',
        ]);
        DB::beginTransaction();
        try {
            // Calculate Total Amount
            $totalAmount = 0;
            foreach ($request->amounts ?? [] as $amount) {
                $totalAmount += (float) ($amount ?? 0);
            }
            // Create or Update Voucher
            $voucher = Voucher::updateOrCreate(
                [
                    'academic_session_id' => $request->academic_session_id,
                    'session_item_id'     => $request->session_item_id,
                ],
                [
                    'due_date'      => $request->due_date,
                    'total_amount'  => $totalAmount,
                    'notes'         => $request->notes,
                ]
            );
            // Remove old items if voucher already exists
            $voucher->items()->delete();
            // Create latest voucher items
            foreach ($request->fee_names ?? [] as $key => $name) {
                $voucher->items()->create([
                    'fee_name' => $name,
                    'amount'   => (float) ($request->amounts[$key] ?? 0),
                ]);
            }
            DB::commit();
            return redirect()
                ->route('admin.voucher.add')
                ->with('success', 'Voucher created/updated successfully.');

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

   // Update / Create Voucher
    public function updateVoucher(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'session_item_id'     => 'required|exists:session_items,id',
            'due_date'            => 'required|date',
            'notes'               => 'nullable|string',
            'fee_names'           => 'nullable|array',
            'amounts'             => 'nullable|array',
        ]);
        DB::beginTransaction();
        try {
            // Find voucher according to Session + Class/Section
            $voucher = Voucher::where('academic_session_id', $request->academic_session_id)
                ->where('session_item_id', $request->session_item_id)
                ->first();
            // If not exists, create new voucher
            if (!$voucher) {
                $voucher = new Voucher();
                $voucher->academic_session_id = $request->academic_session_id;
                $voucher->session_item_id = $request->session_item_id;
            }
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->amounts ?? [] as $amount) {
                $totalAmount += (float) ($amount ?? 0);
            }
            // Save voucher
            $voucher->due_date = $request->due_date;
            $voucher->total_amount = $totalAmount;
            $voucher->notes = $request->notes;
            $voucher->save();
            // Remove old items
            $voucher->items()->delete();
            // Create new items
            foreach ($request->fee_names ?? [] as $key => $name) {
                $voucher->items()->create([
                    'fee_name' => $name,
                    'amount'   => (float) ($request->amounts[$key] ?? 0),
                ]);
            }
            DB::commit();
            return redirect()
                ->route('admin.voucher.index')
                ->with('success', 'Voucher saved successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong! ' . $e->getMessage());
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
