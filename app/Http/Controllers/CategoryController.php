<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function barcodeStickerPrint(Request $request, $id)
    {
        $product = Product::with('supplier')->findOrFail($id);
        $qty     = max(1, (int) $request->query('qty', 1));

        return view('barcode-sticker', compact('product', 'qty'));
    }

    public function barcodeStickerBulkPrint(Request $request)
    {
        $query = $request->input('search');
        $sortOrder = $request->input('sort');
        $selectedSupplier = $request->input('selectedSupplier');
        $stockStatus = $request->input('stockStatus');
        $selectedCategory = $request->input('selectedCategory');

        $productsQuery = Product::with('supplier')
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%");
                });
            })
            ->when($selectedSupplier, function ($queryBuilder) use ($selectedSupplier) {
                $queryBuilder->where('supplier_id', $selectedSupplier);
            })
            ->when(in_array($sortOrder, ['asc', 'desc']), function ($queryBuilder) use ($sortOrder) {
                $queryBuilder->orderBy('selling_price', $sortOrder);
            })
            ->when($stockStatus, function ($queryBuilder) use ($stockStatus) {
                if ($stockStatus === 'in') {
                    $queryBuilder->where('stock_quantity', '>', 0);
                } elseif ($stockStatus === 'out') {
                    $queryBuilder->where('stock_quantity', '<=', 0);
                }
            })
            ->when($selectedCategory, function ($queryBuilder) use ($selectedCategory) {
                $queryBuilder->where('category_id', $selectedCategory);
            });

        $products = $productsQuery->get();

        return view('barcode-sticker-bulk', compact('products'));
    }

    public function index()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }
        Gate::allows('hasRole', ['Admin', 'Manager']);
        // $paginatedcategories = Category::with('parent')->latest()->paginate(10);
        // $allcategories = Category::with('parent')->latest()->get();
        // $allcategories = Category::with('parent')->latest()->get()
        $allcategories = Category::with('parent')
            ->orderBy('created_at', 'desc')
            ->get() // Get the collection
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                    ] : null,
                    'hierarchy_string' => $category->hierarchy_string, // Add this
                ];
            });


        return Inertia::render('Categories/Index', [
            // 'paginatedcategories' => $paginatedcategories,
            'allcategories' => $allcategories,
            'totalCategories' => $allcategories->count()
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Categories/Create', [
            'categories' => $categories,
        ]);
    }

    // public function store(Request $request)
    // {


    //     if (!Gate::allows('hasRole', ['Admin'])) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'parent_id' => 'nullable|exists:categories,id',
    //     ]);


    //     Category::create($validated);

    //     return redirect()->route('categories.index')->banner('Category created successfully.');

    //  }





    public function store(Request $request)
    {


        if ($request->has('categoryName')) {

            $request->merge(['name' => $request->input('categoryName')]);


            $validated = $request->validate([
                'name' => 'required|string|max:191|unique:categories,name',
                'parent_id' => 'nullable|exists:categories,id',
            ]);


            Category::create($validated);
            return redirect()
            ->route('products.index')
            ->with('success', 'Category created successfully and redirected to Products.');
        }

        if ($request->has('name')) {
            // Validate name directly
            $validated = $request->validate([
                'name' => 'required|string|max:191|regex:/^[a-zA-Z\s]+$/|unique:categories,name',
                 'parent_id' => 'nullable|exists:categories,id',
            ]);


            Category::create($validated);


            return redirect()->route('categories.index')->banner('Category created successfully !');
        }

        return redirect()->back()->withErrors(['error' => 'Invalid data provided.']);
    }

    public function quickStore(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'categoryName' => 'required|string|max:191|unique:categories,name',
            'parent_id'    => 'nullable|exists:categories,id',
        ]);

        $category = Category::create([
            'name'      => $validated['categoryName'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return response()->json([
            'id'               => $category->id,
            'name'             => $category->name,
            'hierarchy_string' => null,
        ]);
    }






    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:191|regex:/^[a-zA-Z\s]+$/|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        // Check for circular relationship
        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);

            // Traverse up the hierarchy to check for circular references
            while ($parent) {
                if ($parent->id === $category->id) {
                    return back()->withErrors(['parent_id' => 'A category cannot be its own parent or ancestor.']);
                }
                $parent = $parent->parent; // Assuming a `parent` relationship exists in your Category model
            }
        }

        $category->update($validated);

        return redirect()->route('categories.index')->banner('Category updated successfully.');

        // return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        $category->delete();
        return redirect()->route('categories.index')->banner('Category Deleted successfully.');
    }
}
