<?php

namespace App\Services\ParentPortal;

use App\Helpers\FeeChallanHelper;
use App\Models\FeeChallan;
use App\Models\GeneralSetting;
use App\Models\Parant;
use App\Models\Student;
use App\Services\OneLinkBillService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Everything the parent portal reads, scoped to the signed-in parent.
 *
 * This is the one place that enforces "a parent only sees their own active
 * children and their challans". Controllers go through it rather than
 * querying Student / FeeChallan directly.
 */
class ParentPortal
{
    private ?GeneralSetting $settings = null;
    private bool $settingsLoaded = false;

    public function __construct(private OneLinkBillService $bills)
    {
    }

    public function parent(): Parant
    {
        return auth()->guard('parents')->user();
    }

    /** The parent's active children. */
    public function students(): Builder
    {
        return Student::query()
            ->where('parent_id', $this->parent()->id)
            ->where('status', 'active');
    }

    /** Challans of the parent's active children. */
    public function challans(): Builder
    {
        $parentId = $this->parent()->id;

        return FeeChallan::query()->whereHas('student', function ($q) use ($parentId) {
            $q->where('parent_id', $parentId)->where('status', 'active');
        });
    }

    public function findStudent(int $id): Student
    {
        return $this->students()
            ->with(['parent', 'guardian', 'sibling', 'academicSession', 'sessionItem'])
            ->findOrFail($id);
    }

    public function findChallan(int $id): FeeChallan
    {
        return $this->challans()
            ->with(['items', 'payment', 'student.academicSession', 'student.sessionItem', 'student.parent'])
            ->findOrFail($id);
    }

    public function settings(): ?GeneralSetting
    {
        if (!$this->settingsLoaded) {
            $this->settings = GeneralSetting::first();
            $this->settingsLoaded = true;
        }

        return $this->settings;
    }

    /** The 1Bill / 1LINK consumer number parents enter in their banking app. */
    public function consumerNumber(FeeChallan $challan): string
    {
        return ($this->settings()?->one_bill_prefix ?? '') . ($challan->challan_no ?? $challan->id);
    }

    public function oneBillEnabled(): bool
    {
        return (bool) config('onelink.enabled') && filled($this->settings()?->one_bill_prefix);
    }

    /**
     * Everything a view needs to show one challan: amounts as of today
     * (same figures the bank receives through 1LINK), a parent-friendly
     * status, and what the parent can do with it.
     */
    public function present(FeeChallan $challan): array
    {
        $amounts = $this->bills->amountsFor($challan);
        $status  = $this->status($challan, $amounts['is_late']);

        return [
            'challan'   => $challan,
            'month'     => FeeChallanHelper::formatChallanMonth($challan->month),
            'amounts'   => $amounts,
            'status'    => $status,
            'consumer'  => $this->consumerNumber($challan),
            'due_date'  => $challan->due_date ? \Carbon\Carbon::parse($challan->due_date) : null,
            'can_pay'   => $challan->status === 'pending',
            'is_open'   => in_array($challan->status, ['pending', 'review'], true),
            'via_1link' => $challan->isPaidViaOneLink(),
        ];
    }

    /**
     * Parent-facing status. "tone" maps to a CSS modifier in portal.css.
     */
    public function status(FeeChallan $challan, bool $isLate = false): array
    {
        return match ($challan->status) {
            'pending'  => $isLate
                ? ['key' => 'overdue', 'label' => 'Overdue', 'tone' => 'danger', 'icon' => 'fa-circle-exclamation']
                : ['key' => 'pending', 'label' => 'Unpaid', 'tone' => 'warning', 'icon' => 'fa-clock'],
            'review'   => ['key' => 'review', 'label' => 'Proof under review', 'tone' => 'info', 'icon' => 'fa-hourglass-half'],
            'approved' => ['key' => 'approved', 'label' => 'Paid', 'tone' => 'success', 'icon' => 'fa-circle-check'],
            'expired'  => ['key' => 'expired', 'label' => 'Expired', 'tone' => 'muted', 'icon' => 'fa-ban'],
            default    => ['key' => 'unknown', 'label' => ucfirst((string) $challan->status), 'tone' => 'muted', 'icon' => 'fa-circle'],
        };
    }

    /** Filters offered on the Fees page, keyed by query value. */
    public static function statusFilters(): array
    {
        return [
            'pending'  => 'Unpaid',
            'review'   => 'Under review',
            'approved' => 'Paid',
            'expired'  => 'Expired',
        ];
    }

    /** Office contact details, shared with the public website (config/school.php). */
    public static function contact(): array
    {
        $school = config('school', []);
        $phone  = (string) ($school['phone'] ?? '');
        $wa     = preg_replace('/\D/', '', (string) ($school['whatsapp'] ?? ''));

        return [
            'phone'     => $phone,
            'tel'       => preg_replace('/[^\d+]/', '', $phone),
            // A placeholder number (all zeros after the country code) is not shown.
            'whatsapp'  => ($wa !== '' && trim(substr($wa, 2), '0') !== '') ? $wa : null,
            'email'     => $school['email'] ?? null,
            'hours'     => $school['hours'] ?? null,
            'name'      => $school['name'] ?? config('app.name'),
            'short'     => $school['short_name'] ?? config('app.name'),
        ];
    }
}
