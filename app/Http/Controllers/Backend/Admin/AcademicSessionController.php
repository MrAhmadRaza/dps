<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AcademicSession;
use App\Models\SessionItem;
use Carbon\Carbon;
use DataTables;

class AcademicSessionController extends Controller
{

    // Show Academic Session
    public function index(Request $request): View|JsonResponse
    {
        $pageTitle = 'Academic Session';
        if ($request->ajax()) {
            $query = AcademicSession::latest();
        return Datatables::of($query)
            ->addIndexColumn()
        
            ->addColumn('start_date', function (AcademicSession $session) {
                return Carbon::parse($session->start_date)->format('d M Y') ?? 'N/A';
            })

            ->filterColumn('start_date', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(start_date, '%d %b %Y') LIKE ?", ["%{$keyword}%"]);
            })

             ->addColumn('end_date', function (AcademicSession $session) {
                return Carbon::parse($session->end_date)->format('d M Y') ?? 'N/A';
            })

            ->filterColumn('end_date', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(end_date, '%d %b %Y') LIKE ?", ["%{$keyword}%"]);
            })

           ->addColumn('status', function (AcademicSession $session) {
                if ( $session->status === 'active') {
                    return '<span class="badge bg-primary px-3 py-2 ">
                                <i class="fas fa-check-circle me-1"></i> Active
                            </span>';
                } else {
                    return '<span class="badge bg-danger px-3 py-2 ">
                                <i class="fas fa-times-circle me-1"></i> Inactive
                            </span>';
                }
            })

           ->filterColumn('status', function ($query, $keyword) {
                $keyword = strtolower(trim($keyword));

                if (str_contains($keyword, 'active') && !str_contains($keyword, 'in')) {
                    $query->where('status', 'active'); 
                } 
                elseif (str_contains($keyword, 'inactive') || str_contains($keyword, 'in active') || $keyword === 'in') {
                    $query->where('status', 'inactive');
                } 
                else {
                    // normal number search (0 or 1)
                    $query->where('status', 'like', "%{$keyword}%");
                }
            })
                
            ->addColumn('action', function (AcademicSession $session) {
                $viewRoute = route('admin.academic-session.view.index', $session->id);
                $editRoute = route('admin.academic-session.edit', $session->id);
                $deleteRoute = route('admin.academic-session.destroy', $session->id);
                return  view('backend.partials.session-action',compact('viewRoute','editRoute', 'deleteRoute'))->render();
            })
            ->rawColumns(['start_date','end_date','status','action'])
            ->make(true);
        }
        return view('backend.academic-session.show-session', compact('pageTitle'));
    }

    // Add Academic Session
    public function addAcademicSession(): View
    {
        $pageTitle = 'Add Session';
        $sessions = AcademicSession::with('sessionItems')->where('status','active')->get();
        return view('backend.academic-session.add-session',compact('pageTitle','sessions'));
    }

    // Class and Section
   public function classSection(Request $request, $id = null): JsonResponse
    {
        if ($request->ajax()) {

            $query = SessionItem::with('academicSession')
                ->whereHas('academicSession', function ($q) {
                    $q->where('status', 'active');
                });
            // Edit page 
            if ($id) {
                $query->where('academic_session_id', $id);
            }

            $items = $query->latest()->get();
            return Datatables::of($items)
                ->addIndexColumn()

                ->addColumn('class', function ($item) {
                    return $item->class ?? '-';
                })
                ->addColumn('section', function ($item) {
                    return $item->section ?? '-';
                })

                // ACTION ONLY FOR EDIT
                ->addColumn('action', function ($item) use ($id) {
                    if (!$id) {
                        return ''; 
                    }
                    $editUrl = route('admin.academic-session-item.edit', $item->id);
                    return '<a href="'.$editUrl.'">
                             <i class="fas fa-edit"></i>
                            </a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

     // View Academic Session
    public function viewAcademicSession(int $id): View
    {
        $pageTitle = 'View Academic Session';
        $session =  AcademicSession::with('sessionItems')->findOrFail($id);
        return view('backend.academic-session.view-session',compact('session','pageTitle'));
    }

    //  Store Academic Session
    public function storeAcademicSession(Request $request): RedirectResponse|View
    {
        // Validation
        $validated = $request->validate([
            'session_name' => 'required',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date',
            'status'       => 'required|in:active,inactive',
        ], [
            'session_name.required' => 'Session name is required.',
            'session_name.regex'    => 'Session format must be like 2021-2022.',
            'start_date.required'   => 'Start date is required.',
            'start_date.date'       => 'Start date must be a valid date.',
            'end_date.required'     => 'End date is required.',
            'end_date.date'         => 'End date must be a valid date.',
            'status.required'       => 'Status is required.',
        ]);
        try {
            AcademicSession::create([
                'session_name' => $validated['session_name'],
                'start_date'  => $validated['start_date'],
                'end_date'  => $validated['end_date'],
                'status' => $validated['status'],
            ]);
            return back()->with('success', 'Academic Session added successfully!');
        } catch (\Exception $e) {
            // Log error 
            \Log::error('Academic Session Store Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    // Store Session Items
    public function storeAcademicSessionItem(Request $request): RedirectResponse|View
    {
        $request->validate([
            'academic_session_id' => 'required',
            'section' => 'required|string',
            'class'  => 'required|string',
        ]);
         try {
            SessionItem::create([
                'academic_session_id' => $request->academic_session_id,
                'section' => $request->section,
                'class' => $request->class,
            ]);
            return back()->with('success', 'Academic Session Items added successfully!');
        } catch (\Exception $e) {
            // Log error 
            \Log::error('Academic Session Items Store Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    // Edit Academic Session
    public function editAcademicSession(int $id): View
    {
        $pageTitle = 'Edit Academic Session';
        $session =  AcademicSession::with('sessionItems')->findOrFail($id);
        return view('backend.academic-session.edit-session',compact('session','pageTitle'));
    }

     // Edit Session Item
    public function editAcademicSessionItem(int $id): View
    {
        $pageTitle = 'Edit Session Item';
        $session_items =  SessionItem::findOrFail($id);
        return view('backend.academic-session.session-items.edit-session-items',compact('session_items','pageTitle'));
    }

    public function updateAcademicSession(Request $request , int $id): RedirectResponse
    {
        // Validation
        $validated = $request->validate([
            'session_name' => 'required|regex:/^\d{4}-\d{4}$/',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date',
            'status'       => 'required|in:active,inactive',
        ], [
            'session_name.required' => 'Session name is required.',
            'session_name.regex'    => 'Session format must be like 2021-2022.',
            'start_date.required'   => 'Start date is required.',
            'start_date.date'       => 'Start date must be a valid date.',
            'end_date.required'     => 'End date is required.',
            'end_date.date'         => 'End date must be a valid date.',
            'status.required'       => 'Status is required.',
        ]);
        try {
            AcademicSession::where('id', $id)->update([
                'session_name' => $validated['session_name'],
                'start_date'  => $validated['start_date'],
                'end_date'  => $validated['end_date'],
                'status' => $validated['status'],
            ]);
            return back()->with('success', 'Academic Session updated successfully!');
        } catch (\Exception $e) {
            // Log error 
            \Log::error('Academic Session Update Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    // Update Session Items
    public function updateAcademicSessionItem(Request $request , int $id): RedirectResponse
    {
        // Validation
        $validated = $request->validate([
            'section' => 'required|string',
            'class'  => 'required|string',
        ]);
        try {
            SessionItem::where('id', $id)->update([
                'section' => $validated['section'],
                'class'  => $validated['class'],
            ]);
            return back()->with('success', 'Academic Session updated successfully!');
        } catch (\Exception $e) {
            // Log error 
            \Log::error('Academic Session Item Update Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    // Delete Academic Session
    public function destroyAcademicSession(int $id): RedirectResponse
    {
        try{
            $session = AcademicSession::findOrFail($id);
            $session->sessionItems()->delete();
            $session->delete();
            return back()->with('success', 'Academic Session Delete successfully!');
        }catch (\Exception $e) {
            // Log error 
            \Log::error('Academic Session Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
