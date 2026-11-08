<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Services\ParentPortal\ParentPortal;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(ParentPortal $portal): View
    {
        $students = $portal->students()
            ->with(['sessionItem', 'academicSession'])
            ->withCount(['challans as open_challans_count' => fn ($q) => $q->where('status', 'pending')])
            ->orderBy('name')
            ->get();

        return view('parent.students.index', [
            'pageTitle' => 'Children',
            'students'  => $students,
        ]);
    }

    public function show(ParentPortal $portal, int $id): View
    {
        $student = $portal->findStudent($id);

        $voucher = null;
        if (!$student->discount && $student->academic_session_id && $student->session_item_id) {
            $voucher = Voucher::with('items')
                ->where('academic_session_id', $student->academic_session_id)
                ->where('session_item_id', $student->session_item_id)
                ->first();
        }

        // Brothers and sisters enrolled at DPS under the same parent account
        $siblings = $portal->students()
            ->with('sessionItem')
            ->whereKeyNot($student->id)
            ->orderBy('name')
            ->get();

        $recent = $student->challans()
            ->with('payment')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn ($c) => $portal->present($c));

        return view('parent.students.show', [
            'pageTitle' => $student->name,
            'student'   => $student,
            'voucher'   => $voucher,
            'siblings'  => $siblings,
            'recent'    => $recent,
        ]);
    }
}
