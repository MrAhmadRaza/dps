<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Services\ParentPortal\ParentPortal;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ParentPortal $portal): View
    {
        $students = $portal->students()
            ->with(['sessionItem', 'academicSession'])
            ->orderBy('name')
            ->get();

        // Open challans (unpaid or proof under review), oldest due first
        $open = $portal->challans()
            ->with(['student', 'payment'])
            ->whereIn('status', ['pending', 'review'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->map(fn ($c) => $portal->present($c));

        // Latest paid challan per child, shown when a child has nothing open
        $lastPaid = $portal->challans()
            ->with(['payment'])
            ->where('status', 'approved')
            ->latest('id')
            ->get()
            ->unique('student_id')
            ->keyBy('student_id')
            ->map(fn ($c) => $portal->present($c));

        $children = $students->map(fn ($student) => [
            'student'   => $student,
            'open'      => $open->filter(fn ($p) => $p['challan']->student_id === $student->id)->values(),
            'last_paid' => $lastPaid->get($student->id),
        ]);

        $toPay = $open->filter(fn ($p) => $p['can_pay']);

        return view('parent.dashboard.index', [
            'pageTitle'     => 'Home',
            'parent'        => $portal->parent(),
            'children'      => $children,
            'totalDue'      => $toPay->sum(fn ($p) => $p['amounts']['receivable']),
            'dueCount'      => $toPay->count(),
            'reviewCount'   => $open->where('status.key', 'review')->count(),
            'overdueCount'  => $toPay->where('status.key', 'overdue')->count(),
            // Earliest due date that hasn't passed yet (overdue challans are counted separately)
            'nextDue'       => $toPay->where('status.key', '!=', 'overdue')->pluck('due_date')->filter()->min(),
            'oneBill'       => $portal->oneBillEnabled(),
        ]);
    }
}
