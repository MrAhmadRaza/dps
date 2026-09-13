<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Parant;
use Carbon\Carbon;
use DataTables;

class ParantController extends Controller
{
      // Show Parents
    public function showParent(Request $request): View|JsonResponse
    {
        $pageTitle = 'Parents';
        if ($request->ajax()) {
            // 🔹 Fetch all parents
            $query = Parant::whereHas('students', function ($query) {
                $query->where('status', 'active');
            })->latest();
            return datatables()->of($query)
                // Index column
                ->addIndexColumn()
                // Action Buttons (View / Edit / Delete)
                ->addColumn('action', function ($row) {
                    $passwordRoute = route('admin.parent.password', $row->id);
                    return view('backend.partials.parent-action', compact('passwordRoute'))->render();
                })
                ->rawColumns([ 'action'])
                ->make(true);
        }
        return view('backend.challans.parent.show-parent', compact('pageTitle'));
    }

    // Password
    public function setPassword(int $id): View
    {
        $pageTitle = 'Update Password';
        $passwordId = $id;
        return view('backend.auth.update-password', compact('pageTitle','passwordId'));
    }

     //  Update Password
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|size:4'
        ]);
        $password =  Parant::findOrFail($request->passwordId);
        $hashPassword = Hash::make($request->password);
        $password->update(['password' => $hashPassword]);
        return redirect()->route('admin.parent.show')->with('success','Passsword Update Succesfully');
    }
}
