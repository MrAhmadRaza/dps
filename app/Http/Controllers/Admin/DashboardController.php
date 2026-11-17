<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminPortal\ChallanStatus;
use App\Services\AdminPortal\DashboardStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardStats $stats, ChallanStatus $status): View
    {
        $pageTitle = 'Dashboard';

        return view('admin.dashboard.dashboard', [
            'pageTitle' => $pageTitle,
            'stats'     => $stats->all(),
            'status'    => $status,
        ]);
    }
}
