<?php

namespace App\Services\AdminPortal;

use App\Models\AcademicSession;
use App\Models\FeeChallan;
use App\Models\Student;
use App\Services\OneLinkBillService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Numbers for the staff dashboard. Kept DB-agnostic (works on MySQL and
 * SQLite): monthly sums are added up in PHP over a small window of rows.
 */
class DashboardStats
{
    public function __construct(private OneLinkBillService $bills, private ChallanStatus $status)
    {
    }

    public function all(): array
    {
        $now   = $this->bills->now();
        $month = $now->format('Y-m');

        return [
            'today'           => $now,
            'month_label'     => $now->format('F Y'),
            'students_active' => Student::where('status', 'active')->count(),
            'students_new'    => Student::where('created_at', '>=', $now->copy()->startOfMonth()->utc())->count(),
            'sessions_active' => AcademicSession::where('status', 'active')->count(),
            'this_month'      => $this->monthTotals($month),
            'review_count'    => FeeChallan::where('status', 'review')->count(),
            'overdue_count'   => $this->status->applyFilter(FeeChallan::query(), 'overdue')->count(),
            'review_queue'    => $this->reviewQueue(),
            'trend'           => $this->trend($now, 6),
            'recent_students' => Student::with(['sessionItem', 'academicSession'])->latest()->take(6)->get(),
        ];
    }

    /** Billed / collected / outstanding for challans of one billing month ("Y-m"). */
    public function monthTotals(string $month): array
    {
        $rows = FeeChallan::where('month', 'like', $month . '%')
            ->where('status', '!=', 'expired')
            ->get(['status', 'total_amount']);

        $billed    = (float) $rows->sum('total_amount');
        $collected = (float) $rows->whereIn('status', ['approved', 'paid'])->sum('total_amount');

        return [
            'count'       => $rows->count(),
            'paid_count'  => $rows->whereIn('status', ['approved', 'paid'])->count(),
            'billed'      => $billed,
            'collected'   => $collected,
            'outstanding' => max(0, $billed - $collected),
            'rate'        => $billed > 0 ? (int) round($collected / $billed * 100) : null,
        ];
    }

    /** Proofs uploaded by parents that are waiting for staff, oldest first. */
    public function reviewQueue(int $limit = 6): Collection
    {
        return FeeChallan::with(['student.sessionItem', 'payment'])
            ->where('status', 'review')
            ->oldest('updated_at')
            ->take($limit)
            ->get();
    }

    /**
     * Billed vs collected for the last $months billing months (oldest first).
     *
     * @return array<int, array{key:string,label:string,billed:float,collected:float}>
     */
    public function trend(Carbon $now, int $months): array
    {
        $first = $now->copy()->startOfMonth()->subMonths($months - 1);

        $buckets = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $first->copy()->addMonths($i);
            $buckets[$m->format('Y-m')] = ['key' => $m->format('Y-m'), 'label' => $m->format('M'), 'billed' => 0.0, 'collected' => 0.0];
        }

        FeeChallan::where('month', '>=', $first->format('Y-m'))
            ->where('status', '!=', 'expired')
            ->get(['month', 'status', 'total_amount'])
            ->each(function (FeeChallan $c) use (&$buckets) {
                $key = substr((string) $c->month, 0, 7);
                if (! isset($buckets[$key])) {
                    return;
                }
                $buckets[$key]['billed'] += (float) $c->total_amount;
                if (in_array($c->status, ['approved', 'paid'], true)) {
                    $buckets[$key]['collected'] += (float) $c->total_amount;
                }
            });

        return array_values($buckets);
    }
}
