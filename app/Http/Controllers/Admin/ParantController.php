<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ParantController extends Controller
{
    // Parent accounts (families with at least one active child)
    public function showParent(Request $request): View|JsonResponse
    {
        $pageTitle = 'Parent accounts';

        if ($request->ajax()) {
            $query = Parant::with(['students' => fn ($q) => $q->where('status', 'active')->with('sessionItem')])
                ->whereHas('students', fn ($q) => $q->where('status', 'active'))
                ->select('parents.*')
                ->latest();

            return datatables()->of($query)
                ->addIndexColumn()
                ->editColumn('father_name', fn ($row) => '<div class="name-cell">' . e($row->father_name) . '</div>')
                ->editColumn('father_nic', fn ($row) => '<span class="mono">' . e($row->father_nic) . '</span>')
                ->addColumn('children', function ($row) {
                    return $row->students->map(function ($s) {
                        $cls = $s->sessionItem ? ' <span class="text-muted">(' . e($s->sessionItem->class . '-' . $s->sessionItem->section) . ')</span>' : '';
                        return '<a href="' . e(route('admin.view.admission', $s->id)) . '">' . e($s->name) . '</a>' . $cls;
                    })->implode('<br>');
                })
                ->addColumn('action', function ($row) {
                    $passwordRoute = route('admin.parent.password', $row->id);

                    return view('admin.partials.parent-action', compact('passwordRoute'))->render();
                })
                ->rawColumns(['father_name', 'father_nic', 'children', 'action'])
                ->make(true);
        }

        return view('admin.challans.parent.show-parent', compact('pageTitle'));
    }

    // Reset password form
    public function setPassword(int $id): View
    {
        $pageTitle = 'Reset password';
        $passwordId = $id;
        $parent = Parant::with(['students' => fn ($q) => $q->where('status', 'active')])->findOrFail($id);

        return view('admin.auth.update-password', compact('pageTitle', 'passwordId', 'parent'));
    }

    // Save the new password
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'passwordId' => 'required|integer|exists:parents,id',
            'password'   => 'required|string|min:4|max:50',
        ], [
            'password.min' => 'The password must be at least 4 characters.',
        ]);

        $parent = Parant::findOrFail($request->passwordId);
        $parent->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.parent.show')
            ->with('success', 'Password reset for ' . $parent->father_name . '. Share it with the parent so they can sign in.');
    }
}
