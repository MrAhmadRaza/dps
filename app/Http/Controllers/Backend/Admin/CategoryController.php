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

class CategoryController extends Controller
{
    // All Category show
    public function index(Request $request)
    {
        $pageTitle = 'Categories';
        if ($request->ajax()) {
            $query = Category::query();
            // Type Filter
            if ($request->type) {
                $query->where('type', $request->type);
            }
            //  SUM of Head Amounts with Date Filter
            $query->withSum(['headCategories as total_amount' => function ($q) use ($request) {

                if ($request->from_date) {
                    $q->whereDate('date', '>=', $request->from_date);
                }

                if ($request->to_date) {
                    $q->whereDate('date', '<=', $request->to_date);
                }
            }], 'amount');

            return datatables()->of($query)
                ->addIndexColumn()

               ->addColumn('category_name', function ($row) {
                    $url = route('admin.categories.headcategory', $row->id);
                    return '<a href="'.$url.'" class="text-primary fw-bold">'
                            .$row->category_name.
                        '</a>';
                })
                
                ->addColumn('total_amount', function ($row) {
                    return number_format($row->total_amount ?? 0, 2);
                })
                ->addColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('d M Y');
                })

                ->addColumn('action', function (Category $category) {
                    $headCategoryRoute = route('admin.categories.headcategory', $category->id);
                    $printHeadCategory = route('admin.categories.head.print', $category->id);
                    $editRoute = route('admin.categories.edit', $category->id);
                    $deleteRoute = route('admin.categories.destroy', $category->id);

                    return view('backend.partials.category-action',
                        compact('headCategoryRoute','printHeadCategory','editRoute','deleteRoute')
                    )->render();
                })

                ->rawColumns(['category_name','action'])
                ->make(true);
        }

        return view('backend.category.show-category',compact('pageTitle'));
    }

    // Print Category
    public function printCategory(Request $request)
    {
        $query = Category::query();
        if ($request->type) {
            $query->where('type', $request->type);
        }
        $query->withSum(['headCategories as total_amount' => function ($q) use ($request) {

            if ($request->from_date) {
                $q->whereDate('date', '>=', $request->from_date);
            }

            if ($request->to_date) {
                $q->whereDate('date', '<=', $request->to_date);
            }

        }], 'amount');
        $categories = $query->get();
        $incomes = $categories->where('type', 'income');
        $expenses = $categories->where('type', 'expense');
        $totalIncome = $incomes->sum('total_amount');
        $totalExpense = $expenses->sum('total_amount');
        return view('backend.category.category-print', compact(
            'categories',
            'incomes',
            'expenses',
            'totalIncome',
            'totalExpense'
        ));
    }

    // Add Category 
    public function addCategory(): View
    {
        $pageTitle = 'Add Categoty';
        return view('backend.category.add-category',compact('pageTitle'));
    }

    
    // Store Category
    public function storeCategory(Request $request): RedirectResponse|View
    {
        try{
            $request->validate([
                'category_name' => 'required|string',
                'type' => 'required|in:income,expense',
            ]);
            Category::create(['category_name' => $request->category_name,'type' => $request->type]);
            return redirect()->back()->with('success', 'Category add successfully');
        }catch (\Exception $e) {
            \Log::error('Add Category error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }


    // Edit Category
    public function editCategory(int $id): View
    {
        $pageTitle = 'pageTitle';
        $category = Category::find($id);
        return view('backend.category.edit-category',compact('pageTitle','category'));
    }

    // Update Category
    public function updateCategory(Request $request , int $id): RedirectResponse|View
    {
         try{
            $request->validate([
                'category_name' => 'required|string',
                'type' => 'required|in:income,expense',
            ]);
            $category = Category::find($id);
            $category->update([
                'category_name' => $request->category_name,
                'type' => $request->type
            ]);
            return redirect()->back()->with('success', 'Category update successfully');
        }catch (\Exception $e) {
            \Log::error('Update Category error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // Delete Category
    public function destroyCategory(int $id): RedirectResponse|View
    {
        try{
            $category = Category::findOrFail($id);
            $category->delete();
            return redirect()->back()->with('success','Delete Category Successfully');
        }catch (\Exception $e) {
            \Log::error('Delete  Category error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    // View
    public function Reports(): View
    {
        $pageTitle = 'Reports';
        return view('backend.category.reports.index',compact('pageTitle'));
    }

    //  Cash Book Report (Month Wise)
    public function cashbook(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'year'  => 'required|numeric'
        ]);

        $month = (int) $request->month;
        $year  = $request->year;

        $monthName = Carbon::create()->month($month)->format('F');
        $heads = HeadCategory::with('categories')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        // Income
        $income = $heads->filter(fn($item) =>
            $item->categories && $item->categories->type === 'income'
        );

        // Expense
        $expense = $heads->filter(fn($item) =>
            $item->categories && $item->categories->type === 'expense'
        );

        // Totals
        $totalIncome  = $income->sum('amount');
        $totalExpense = $expense->sum('amount');

        $netBalance = $totalIncome - $totalExpense;

        // Group by date
        $groupedIncome = $income->groupBy(fn($item) =>
            Carbon::parse($item->date)->format('d/m/Y')
        );

        $groupedExpense = $expense->groupBy(fn($item) =>
            Carbon::parse($item->date)->format('d/m/Y')
        );

        return view('backend.category.reports.cashbook-print', compact(
            'income',
            'expense',
            'groupedIncome',
            'groupedExpense',
            'totalIncome',
            'totalExpense',
            'netBalance',
            'monthName',
            'year'
        ));
    }
}
