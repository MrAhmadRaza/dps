<?php

namespace App\Http\Controllers\Parent;

use App\Helpers\FeeChallanHelper;
use App\Http\Controllers\Controller;
use App\Models\ChallanPayment;
use App\Services\ParentPortal\ParentPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChallanController extends Controller
{
    /** Where payment proofs are stored (relative to public/), shared with the admin portal. */
    public const PROOF_DIR = 'upload/challans';

    /** Banks suggested on the upload form; any name can still be typed. */
    public const BANKS = [
        'Bank of Punjab', 'HBL', 'Meezan Bank', 'MCB Bank', 'United Bank (UBL)', 'Allied Bank',
        'Bank Alfalah', 'National Bank of Pakistan', 'Askari Bank', 'Faysal Bank', 'Bank Al Habib',
        'JS Bank', 'Standard Chartered', 'JazzCash', 'Easypaisa',
    ];

    public function index(Request $request, ParentPortal $portal): View
    {
        $students = $portal->students()->orderBy('name')->get(['id', 'name']);

        $studentId = $request->integer('student') ?: null;
        if ($studentId && !$students->contains('id', $studentId)) {
            $studentId = null;
        }

        $status = $request->query('status');
        if (!array_key_exists((string) $status, ParentPortal::statusFilters())) {
            $status = null;
        }

        $challans = $portal->challans()
            ->with(['student.sessionItem', 'payment'])
            ->when($studentId, fn ($q) => $q->where('student_id', $studentId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $challans->setCollection($challans->getCollection()->map(fn ($c) => $portal->present($c)));

        return view('parent.challans.index', [
            'pageTitle' => 'Fees',
            'students'  => $students,
            'studentId' => $studentId,
            'status'    => $status,
            'challans'  => $challans,
            'filters'   => ParentPortal::statusFilters(),
        ]);
    }

    public function show(ParentPortal $portal, int $id): View
    {
        $challan = $portal->findChallan($id);

        return view('parent.challans.show', [
            'pageTitle' => 'Challan ' . ($challan->challan_no ?? $challan->id),
            'p'         => $portal->present($challan),
            'breakdown' => FeeChallanHelper::getPrintBreakdown($challan),
            'proof'     => $this->proofUrl($challan->payment?->voucher_image),
            'oneBill'   => $portal->oneBillEnabled(),
        ]);
    }

    public function uploadForm(ParentPortal $portal, int $id): View|RedirectResponse
    {
        $challan = $portal->findChallan($id);

        if ($challan->status !== 'pending') {
            return redirect()->route('parent.view.challan', $challan->id)
                ->with('error', 'A payment proof can only be uploaded for an unpaid challan.');
        }

        return view('parent.challans.upload', [
            'pageTitle' => 'Upload payment proof',
            'p'         => $portal->present($challan),
            'banks'     => self::BANKS,
        ]);
    }

    public function upload(Request $request, ParentPortal $portal): RedirectResponse
    {
        $validated = $request->validate([
            'fee_challan_id' => 'required|integer',
            'bank_name'      => 'required|string|max:255',
            'paid_date'      => 'required|date|before_or_equal:today',
            'transaction_id' => 'nullable|string|max:255',
            'voucher_image'  => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'bank_name.required'        => 'Enter the bank or wallet you paid through.',
            'paid_date.before_or_equal' => 'The payment date can\'t be in the future.',
            'voucher_image.required'    => 'Add a photo of the paid challan or the payment receipt.',
            'voucher_image.image'       => 'The proof must be a photo (JPG or PNG).',
            'voucher_image.mimes'       => 'The proof must be a JPG or PNG photo.',
            'voucher_image.max'         => 'The photo is larger than 2 MB. Try a smaller photo or a screenshot.',
        ]);

        // Ownership: findChallan only returns challans of this parent's active children.
        $challan = $portal->findChallan((int) $validated['fee_challan_id']);

        if ($challan->isPaidViaOneLink()) {
            return redirect()->route('parent.view.challan', $challan->id)
                ->with('error', 'This challan has already been paid through 1LINK.');
        }
        if ($challan->status !== 'pending') {
            return redirect()->route('parent.view.challan', $challan->id)
                ->with('error', 'A payment proof can only be uploaded for an unpaid challan.');
        }

        $dir = public_path(self::PROOF_DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file     = $request->file('voucher_image');
        $filename = Str::random(40) . '.' . $file->extension();
        $newPath  = self::PROOF_DIR . '/' . $filename;

        try {
            $file->move($dir, $filename);

            $oldPath = DB::transaction(function () use ($challan, $validated, $newPath) {
                $payment = ChallanPayment::withTrashed()->where('fee_challan_id', $challan->id)->first()
                    ?? new ChallanPayment(['fee_challan_id' => $challan->id]);

                if ($payment->trashed()) {
                    $payment->restore();
                }

                $oldPath = $payment->voucher_image;

                $payment->fill([
                    'source'         => ChallanPayment::SOURCE_MANUAL,
                    'bank_name'      => $validated['bank_name'],
                    'paid_date'      => $validated['paid_date'],
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'voucher_image'  => $newPath,
                ])->save();

                $challan->update(['status' => 'review']);

                return $oldPath;
            });
        } catch (\Throwable $e) {
            Log::error('Parent portal: payment proof upload failed', ['challan' => $challan->id, 'error' => $e->getMessage()]);
            if (is_file(public_path($newPath))) {
                @unlink(public_path($newPath));
            }

            return back()->withInput()->with('error', 'The payment proof could not be uploaded. Please try again.');
        }

        // Replace an earlier proof for this challan (e.g. after the school asked for a new one)
        if ($oldPath && $oldPath !== $newPath && str_starts_with($oldPath, self::PROOF_DIR . '/') && is_file(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        return redirect()->route('parent.view.challan', $challan->id)
            ->with('success', 'Payment proof sent. The school office will confirm it shortly.');
    }

    public function print(ParentPortal $portal, int $id): View
    {
        $challan = $portal->findChallan($id);

        return view('parent.challans.print', [
            'challan'    => $challan,
            'student'    => $challan->student,
            'monthLabel' => FeeChallanHelper::formatChallanMonth($challan->month),
            'breakdown'  => FeeChallanHelper::getPrintBreakdown($challan),
        ]);
    }

    private function proofUrl(?string $path): ?string
    {
        return $path && is_file(public_path($path)) ? asset($path) : null;
    }
}
