<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request,JsonResponse,RedirectResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\HeadCategory;
use Carbon\Carbon;
use DataTables;

class HeadCategoryController extends Controller
{

    //All Head under Category show
    public function index(Request $request , int $id): View|JsonResponse
    {
        $pageTitle = 'Categories';
        if ($request->ajax()) {
            $query = HeadCategory::where('category_id',$id)->latest();
            //  DATE RANGE FILTER
            if ($request->from_date) {
                $query->whereDate('date', '>=', $request->from_date);
            }

            if ($request->to_date) {
                $query->whereDate('date', '<=', $request->to_date);
            }
        return Datatables::of($query)
            ->addIndexColumn()

            
             ->addColumn('date', function (HeadCategory $category) {
                return Carbon::parse($category->date)->format('d M Y') ?? 'N/A';
            })

            ->addColumn('created_at', function (HeadCategory $category) {
                return Carbon::parse($category->created_at)->format('d M Y') ?? 'N/A';
            })

            // Date Filter
            ->filterColumn('date', function ($query, $keyword) {
                $keyword = trim($keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->whereRaw("DATE_FORMAT(CONVERT_TZ(date, '+00:00', '+01:00'), '%d %b %Y, %h:%i %p') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(date, '%d %b %Y') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(date, '%M %Y') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(date, '%d %M') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(date, '%h:%i %p') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(date, '%Y-%m-%d') LIKE ?", ["%$keyword%"]);
                });
            })

            ->filterColumn('created_at', function ($query, $keyword) {
                $keyword = trim($keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->whereRaw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '+01:00'), '%d %b %Y, %h:%i %p') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%d %b %Y') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%M %Y') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%d %M') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%h:%i %p') LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%$keyword%"]);
                });
            })
            ->addColumn('action', function (HeadCategory $category) {
                $editRoute = route('admin.categories.edithead', $category->id);
                $deleteRoute = route('admin.categories.destroyhead', $category->id);
                return  view('backend.partials.head-category-action',compact('editRoute', 'deleteRoute'))->render();
            })
            ->rawColumns(['date','created_at','action'])
            ->make(true);
        }
    }
    // Add Category 
    public function addHeadCategory(int $id): View
    {
        $pageTitle = 'Head Categoty';
        $getCategories = Category::get();
        $category =  $getCategories->find($id);
        return view('backend.category.add-head-category',compact('pageTitle','category','getCategories'));
    }

    // Store Head Category
    public function storeHeadCategory(Request $request): RedirectResponse|View
    {
        try{
            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'head_name' => 'required',
                'date' => 'date',
                'amount' => 'required|numeric',
            ]);
            HeadCategory::create([
                'category_id' => $request->category_id,
                'head_name' => $request->head_name,
                'bill_no' =>  $request->bill_no,
                'date' => $request->date,
                'amount' => $request->amount ,
            ]);
            return redirect()->back()->with('success', 'Head Category add successfully');
        }catch (\Exception $e) {
            \Log::error('Add Head Category error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // Edit Head Category
    public function editHeadCategory(int $id): View
    {
        $pageTitle = 'Edit Head Category';
        $headCategory = HeadCategory::find($id);
        return view('backend.category.edit-head-category',compact('pageTitle','headCategory'));
    }

    public function updateHeadCategory(Request $request , int $id): RedirectResponse|View
    {
        try{
             $request->validate([
                'head_name' => 'required',
                'date' => 'date',
                'amount' => 'required|numeric',
            ]);
            $headCategory = HeadCategory::find($id);
            $headCategory->update([
                'head_name' => $request->head_name,
                'bill_no' =>  $request->bill_no,
                'date' => $request->date,
                'amount' => $request->amount ,
            ]);
            return redirect()->back()->with('success', 'Head Category update successfully');
        }catch (\Exception $e) {
            \Log::error('Update Head Category error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // Delete HeadCategory
    public function destroyHeadCategory(int $id): RedirectResponse|View
    {
        try{
            $headCategory = HeadCategory::find($id);
            $headCategory->delete();
            return redirect()->back()->with('success','Delete Head Category Successfully');
        }catch (\Exception $e) {
            \Log::error('Delete Head Category error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // Print Head under category
    public function printHeads($id)
    {
        $category = Category::findOrFail($id);
        $heads = HeadCategory::where('category_id', $id)->get();
        return view('backend.category.head-category-print', compact('category', 'heads'));
    }
}
