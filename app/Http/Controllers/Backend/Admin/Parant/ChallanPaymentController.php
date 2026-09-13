<?php

namespace App\Http\Controllers\Backend\Admin\Parant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\ChallanPayment;
use App\Models\FeeChallan;

class ChallanPaymentController extends Controller
{
    public function uploadChallan(Request $request)
    {
        try {
            $request->validate([
                'fee_challan_id'   => 'required|exists:fee_challans,id',
                'bank_name'        => 'required|string|max:255',
                'paid_date'        => 'required|date',
                'transaction_id'   => 'nullable|string|max:255',
                'voucher_image'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);
            $challanPayment = ChallanPayment::where('fee_challan_id', $request->fee_challan_id)->first();
            $imagePath = null;
            // ========== Image Upload ==========
            if ($request->hasFile('voucher_image')) {

                $file     = $request->file('voucher_image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/challans'), $filename);
                $imagePath = 'upload/challans/' . $filename;
                if ($challanPayment && $challanPayment->voucher_image) {
                    $oldImagePath = public_path($challanPayment->voucher_image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }
            // ========== Update or Create ==========
            if ($challanPayment) {
                // Record already exist → Update
                $challanPayment->update([
                    'bank_name'      => $request->bank_name,
                    'paid_date'      => $request->paid_date,
                    'transaction_id' => $request->transaction_id,
                    'voucher_image'  => $imagePath,          
                ]);
            } else {
                // Naya record create
                ChallanPayment::create([
                    'fee_challan_id' => $request->fee_challan_id,
                    'bank_name'      => $request->bank_name,
                    'paid_date'      => $request->paid_date,
                    'transaction_id' => $request->transaction_id,
                    'voucher_image'  => $imagePath,
                ]);
            }
             // FeeChallan status change
            FeeChallan::where('id', $request->fee_challan_id)->update([
                'status' => 'review'
            ]);

            return back()->with('success', 'Voucher uploaded successfully');

        } catch (\Exception $e) {
            \Log::error('Challan upload error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
    
    // View Challan 
    public function viewChallan(int $id): View|RedirectResponse
    {
        try {
            $parentId = auth()->guard('parents')->id();
            $challan = FeeChallan::with([
                'student.academicSession',
                'student.sessionItem',
                'payment'
            ])->where('id', $id)
            ->whereHas('student', function ($query) use ($parentId) {
                $query->where('parent_id', $parentId)
                    ->where('status', 'active');
            })->firstOrFail();

            $pageTitle = 'View Challan';
            return view('backend.parent.fee-challan.challan-view', compact('challan', 'pageTitle'));
        } catch (\Exception $e) {
            \Log::error('View Challan error: ' . $e->getMessage());
            return back()->with('error', 'Challan record not found or something went wrong');
        }
    }
}
