<?php

namespace App\Services\AdminPortal;

use App\Models\ChallanPayment;
use App\Models\FeeChallan;
use App\Services\OneLinkBillService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Staff-facing challan status: one place for the labels, colours and the
 * status filters on the Challans page. Uses the same words as the parent
 * portal (Unpaid, Overdue, Needs review, Paid, Expired) so staff and parents
 * describe a challan the same way.
 *
 * Stored statuses are unchanged: pending / review / approved / expired.
 * "Overdue" is derived: pending and past the due date (Pakistan time).
 */
class ChallanStatus
{
    /** Filter tabs on the Challans page, in order. key => label */
    public const FILTERS = [
        'all'     => 'All',
        'review'  => 'Needs review',
        'unpaid'  => 'Unpaid',
        'overdue' => 'Overdue',
        'paid'    => 'Paid',
        'expired' => 'Expired',
    ];

    public function __construct(private OneLinkBillService $bills)
    {
    }

    public function today(): string
    {
        return $this->bills->now()->toDateString();
    }

    /**
     * @return array{key:string,label:string,tone:string,icon:string,onelink:bool}
     */
    public function for(FeeChallan $challan): array
    {
        $onelink = $challan->payment?->source === ChallanPayment::SOURCE_ONELINK;

        $state = match ($challan->status) {
            'pending'  => $this->isOverdue($challan)
                ? ['overdue', 'Overdue', 'danger', 'fa-circle-exclamation']
                : ['unpaid', 'Unpaid', 'warning', 'fa-hourglass-half'],
            'review'   => ['review', 'Needs review', 'info', 'fa-magnifying-glass'],
            'approved', 'paid' => ['paid', 'Paid', 'success', 'fa-circle-check'],
            'expired'  => ['expired', 'Expired', 'muted', 'fa-ban'],
            default    => ['unknown', ucfirst((string) $challan->status ?: 'Unknown'), 'muted', 'fa-circle-question'],
        };

        return [
            'key'     => $state[0],
            'label'   => $state[1],
            'tone'    => $state[2],
            'icon'    => $state[3],
            'onelink' => $onelink && $state[0] === 'paid',
        ];
    }

    public function isOverdue(FeeChallan $challan): bool
    {
        return $challan->status === 'pending'
            && $challan->due_date
            && substr((string) $challan->due_date, 0, 10) < $this->today();
    }

    /** HTML chip for tables (DataTables raw column). */
    public function chip(FeeChallan $challan): string
    {
        return view('admin.partials.status-chip', ['status' => $this->for($challan)])->render();
    }

    /** Restrict a challan query to one of the FILTERS keys. Unknown keys mean "all". */
    public function applyFilter(Builder $query, ?string $filter): Builder
    {
        $today = $this->today();

        return match ($filter) {
            'review'  => $query->where('status', 'review'),
            'unpaid'  => $query->where('status', 'pending')
                ->where(fn ($q) => $q->whereNull('due_date')->orWhereDate('due_date', '>=', $today)),
            'overdue' => $query->where('status', 'pending')->whereDate('due_date', '<', $today),
            'paid'    => $query->whereIn('status', ['approved', 'paid']),
            'expired' => $query->where('status', 'expired'),
            default   => $query,
        };
    }

    /** Number of challans under each filter tab. */
    public function counts(): array
    {
        $counts = [];
        foreach (array_keys(self::FILTERS) as $key) {
            $counts[$key] = $this->applyFilter(FeeChallan::query(), $key)->count();
        }

        return $counts;
    }
}
