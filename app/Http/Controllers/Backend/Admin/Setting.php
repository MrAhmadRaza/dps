<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,RedirectResponse};
use Illuminate\View\View;
use App\Models\GeneralSetting;

class Setting extends Controller
{
    // index
    public function index(): View
    {
        $pageTitle = 'settings';
        $settings = GeneralSetting::first();
        return view('backend.settings.index',compact('pageTitle','settings'));
    }

    // Store Setting
    public function storeSettings(Request $request)
    {
        $valdiated = $request->validate([
            'setting_id' => 'required|exists:general_settings,id',
            'name' => 'required|string',
            'account_no' => 'required|numeric',
            'one_bill_prefix' => ['nullable', 'string', 'max:50'],
            'late_fee_fine' => 'nullable'
        ]);
        GeneralSetting::where('id',$valdiated['setting_id'])->update([
            'name' => $valdiated['name'],
            'account_no' => $valdiated['account_no'],
            'one_bill_prefix' => $valdiated['one_bill_prefix'],
            'late_fee_fine' => $valdiated['late_fee_fine'],
        ]);
        return back()->with('success','Settings saved successfully');
    }
}
