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
        try{
           $request->validate([
                'fee_challan_id' => 'required|exists:fee_challans,id',
                'bank_name' => 'required|string|max:255',
                'paid_date' => 'required|date',
                'voucher_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Check if a record already exists
            $challanPayment = ChallanPayment::where('fee_challan_id', $request->fee_challan_id)->first();
            $imagePath = null;
            if ($request->hasFile('voucher_image')) {
                $file = $request->file('voucher_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('upload/challans'), $filename);
                $imagePath = 'upload/challans/' . $filename;

                // Delete old image if exists
                if ($challanPayment && $challanPayment->voucher_image && file_exists(public_path($challanPayment->voucher_image))) {
                    unlink(public_path($challanPayment->voucher_image));
                }
            }
            if ($challanPayment) {
                // Update existing record
                $challanPayment->update([
                    'bank_name' => $request->bank_name,
                    'paid_date' => $request->paid_date,
                    'transaction_id' => $request->transaction_id,
                    'voucher_image' => $imagePath,
                    'status' => 'pending', 
                ]);
            } else {
                // Create new record
                 ChallanPayment::create([
                    'fee_challan_id' => $request->fee_challan_id,
                    'bank_name' => $request->bank_name,
                    'paid_date' => $request->paid_date,
                    'transaction_id' => $request->transaction_id,
                    'voucher_image' => $imagePath,
                ]);
                FeeChallan::where('id', $request->fee_challan_id)->update(['status' => 'review']);
            }
            return back()->with('success', 'Voucher uploaded successfully');
        }catch (\Exception $e) {
            \Log::error('Challan upload error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }   
    } 

    // View challan
    public function viewChallan(int $id): View
    {
        $pageTitle = 'View Challan';
        $challan = ChallanPayment::where('fee_challan_id', $id)
        ->whereHas('challan.student', function ($q) {
            $q->where('parent_id', auth()->guard('parents')->user()->id);
        })
        ->firstOrFail();
        return view('backend.parent.fee-challan.challan-view', compact('challan','pageTitle'));
    }
}
