<?php

namespace App\Services\AdminPortal;

use App\Models\FeeChallan;
use Illuminate\Http\Request;

/**
 * The one list of staff-portal sections. The sidebar and the breadcrumb
 * both read it, so adding a page means adding it here once.
 */
class Navigation
{
    public static function groups(): array
    {
        return [
            'Overview' => [
                ['label' => 'Dashboard', 'icon' => 'fa-gauge-high', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
            ],
            'Students' => [
                ['label' => 'Admissions', 'icon' => 'fa-user-graduate', 'route' => 'admin.admission.index',
                 'active' => ['admin.admission.*', 'admin.view.admission', 'admin.add.admission', 'admin.edit.admission']],
                ['label' => 'Academic sessions', 'icon' => 'fa-calendar-days', 'route' => 'admin.academic-session.index',
                 'active' => ['admin.academic-session.*', 'admin.academic-session-item.*']],
            ],
            'Fees' => [
                ['label' => 'Challans', 'icon' => 'fa-file-invoice-dollar', 'route' => 'admin.challan.index',
                 'active' => ['admin.challan.*'], 'count' => FeeChallan::where('status', 'review')->count()],
                ['label' => 'Fee vouchers', 'icon' => 'fa-receipt', 'route' => 'admin.voucher.index', 'active' => ['admin.voucher.*']],
                ['label' => 'Parent accounts', 'icon' => 'fa-people-roof', 'route' => 'admin.parent.show', 'active' => ['admin.parent.*']],
            ],
            'Accounts' => [
                ['label' => 'Categories', 'icon' => 'fa-layer-group', 'route' => 'admin.categories.index', 'active' => ['admin.categories.*']],
                ['label' => 'Cashbook report', 'icon' => 'fa-book', 'route' => 'admin.reports.print', 'active' => ['admin.reports.*']],
            ],
            'System' => [
                ['label' => 'Settings', 'icon' => 'fa-gear', 'route' => 'admin.general-settings.index', 'active' => ['admin.general-settings.*']],
            ],
        ];
    }

    /** The section the current page belongs to, with its group name. */
    public static function current(array $groups, Request $request): ?array
    {
        foreach ($groups as $group => $items) {
            foreach ($items as $item) {
                if ($request->routeIs(...$item['active'])) {
                    return ['group' => $group] + $item;
                }
            }
        }

        return null;
    }
}
