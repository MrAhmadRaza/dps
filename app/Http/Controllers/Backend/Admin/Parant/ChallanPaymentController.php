<?php

namespace App\Http\Controllers\Backend\Admin\Parant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\ChallanPayment;
use App\Models\FeeChallan;
use Illuminate\Support\Str;

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
            // Only the logged-in parent's own active children, and only unpaid challans
            $parentId = auth()->guard('parents')->id();
            $challan = FeeChallan::where('id', $request->fee_challan_id)
                ->whereHas('student', function ($query) use ($parentId) {
                    $query->where('parent_id', $parentId)->where('status', 'active');
                })->first();
            if (!$challan) {
                return back()->with('error', 'Challan not found.');
            }
            if ($challan->status !== 'pending') {
                return back()->with('error', 'A payment proof can only be uploaded for an unpaid challan.');
            }

            $challanPayment = ChallanPayment::withTrashed()->where('fee_challan_id', $challan->id)->first();
            if ($challanPayment && $challanPayment->source === ChallanPayment::SOURCE_ONELINK) {
                return back()->with('error', 'This challan has already been paid through 1LINK.');
            }
            if ($challanPayment && $challanPayment->trashed()) {
                $challanPayment->restore();
            }
            $imagePath = null;
            // ========== Image Upload ==========
            if ($request->hasFile('voucher_image')) {

                $file     = $request->file('voucher_image');
                $filename = Str::random(40) . '.' . $file->extension();
                $file->move(public_path('upload/challans'), $filename);
                $imagePath = 'upload/challans/' . $filename;
                if ($challanPayment && $challanPayment->voucher_image) {
                    $oldImagePath = public_path($challanPayment->voucher_image);
                    if (is_file($oldImagePath)) {
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
                    'fee_challan_id' => $challan->id,
                    'bank_name'      => $request->bank_name,
                    'paid_date'      => $request->paid_date,
                    'transaction_id' => $request->transaction_id,
                    'voucher_image'  => $imagePath,
                ]);
            }
             // FeeChallan status change
            $challan->update(['status' => 'review']);

            return back()->with('success', 'Voucher uploaded successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Challan upload error: ' . $e->getMessage());
            return back()->with('error', 'The payment proof could not be uploaded. Please try again.');
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
