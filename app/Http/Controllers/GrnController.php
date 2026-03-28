<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GrnController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $query = $request->input('search');
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        $grns = Grn::with('supplier', 'createdBy')
            ->withCount('items')
            ->when($query, fn($q) => $q->where('grn_number', 'like', "%{$query}%")
                ->orWhere('reference_no', 'like', "%{$query}%"))
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->when($status, fn($q) => $q->where('payment_status', $status))
            ->when($from, fn($q) => $q->whereDate('grn_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('grn_date', '<=', $to))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $totals = [
            'total_grns' => Grn::count(),
            'total_value' => Grn::sum('total_amount'),
            'total_paid' => Grn::sum('paid_amount'),
            'total_outstanding' => Grn::sum('total_amount') - Grn::sum('paid_amount'),
        ];

        return Inertia::render('Grn/Index', [
            'grns' => $grns,
            'suppliers' => $suppliers,
            'totals' => $totals,
            'filters' => $request->only(['search', 'supplier_id', 'status', 'from', 'to']),
        ]);
    }

    public function create()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'contact', 'email']);
        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Grn/Create', [
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'supplier_id'       => 'required|exists:suppliers,id',
            'grn_date'          => 'required|date',
            'reference_no'      => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.is_new_product' => 'nullable|boolean',
            // New product fields
            'items.*.name' => 'required_if:items.*.is_new_product,true|string|max:255',
            'items.*.category_id' => 'nullable|exists:categories,id',
            'items.*.new_category_name' => 'nullable|string|max:255',
            'items.*.barcode' => 'nullable|string|max:100',
            'items.*.selling_price' => 'required_if:items.*.is_new_product,true|numeric|min:0',
            // Existing fields
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $grnNumber = $this->generateGrnNumber();

            $totalAmount = collect($validated['items'])->sum(
                fn($item) => $item['unit_cost'] * $item['quantity']
            );

            $grn = Grn::create([
                'grn_number'     => $grnNumber,
                'supplier_id'    => $validated['supplier_id'],
                'grn_date'       => $validated['grn_date'],
                'reference_no'   => $validated['reference_no'] ?? null,
                'total_amount'   => $totalAmount,
                'paid_amount'    => 0,
                'payment_status' => 'unpaid',
                'notes'          => $validated['notes'] ?? null,
                'created_by'     => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                // Check if this is a new product that needs to be created
                if (!empty($item['is_new_product']) && $item['is_new_product'] === true) {
                    // Generate barcode if not provided, encoding the cost
                    if (empty($item['barcode'])) {
                        $item['barcode'] = $this->generateUniqueBarcode($item['unit_cost']);
                    }

                    // Handle category - create new if provided, or use existing
                    $categoryId = null;
                    if (!empty($item['new_category_name'])) {
                        // Create new category
                        $category = Category::firstOrCreate(
                            ['name' => trim($item['new_category_name'])],
                            ['name' => trim($item['new_category_name'])]
                        );
                        $categoryId = $category->id;
                    } elseif (!empty($item['category_id'])) {
                        // Use existing category
                        $categoryId = $item['category_id'];
                    }

                    // Create new product
                    $product = Product::create([
                        'name' => $item['name'],
                        'barcode' => $item['barcode'],
                        'category_id' => $categoryId,
                        'supplier_id' => $validated['supplier_id'],
                        'cost_price' => $item['unit_cost'],
                        'selling_price' => $item['selling_price'],
                        'stock_quantity' => $item['quantity'],
                        'total_quantity' => $item['quantity'],
                        'purchase_date' => $validated['grn_date'],
                    ]);

                    $item['product_id'] = $product->id;
                } else {
                    // Existing product - update it
                    $product = Product::find($item['product_id']);
                    $product->stock_quantity += $item['quantity'];
                    $product->total_quantity = ($product->total_quantity ?? 0) + $item['quantity'];
                    $product->cost_price = $item['unit_cost'];
                    $product->purchase_date = $validated['grn_date'];
                    $product->save();
                }

                $lineTotal = $item['unit_cost'] * $item['quantity'];

                GrnItem::create([
                    'grn_id'      => $grn->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_cost'   => $item['unit_cost'],
                    'total_cost'  => $lineTotal,
                ]);
            }

            return redirect()->route('grn.show', $grn->id)
                ->banner('GRN ' . $grnNumber . ' created successfully.');
        });
    }

    /**
     * Generate 12-digit Sri Lankan barcode format with cost encoding
     * Format: XXX-CCCC-PPPP-C
     * - First 3 digits: prefix (955 for Sri Lanka)
     * - Next 4 digits: encoded cost price
     * - Next 4 digits: random product identifier
     * - Last digit: check digit
     */
    private function generateUniqueBarcode(float $costPrice = 0): string
    {
        do {
            $prefix = '955'; // Sri Lankan prefix
            
            // Encode cost in 4 digits
            $costEncoded = str_pad((string) round($costPrice), 4, '0', STR_PAD_LEFT);
            $costEncoded = substr($costEncoded, -4); // Take last 4 digits
            
            // Random 4-digit product identifier
            $productId = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Calculate check digit (modulo 10)
            $digits = $prefix . $costEncoded . $productId;
            $sum = 0;
            for ($i = 0; $i < strlen($digits); $i++) {
                $sum += (int) $digits[$i] * (($i % 2 === 0) ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            
            $barcode = $digits . $checkDigit;
        } while (Product::where('barcode', $barcode)->exists());
        
        return $barcode;
    }

    public function show(Grn $grn)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $grn->load([
            'supplier',
            'createdBy',
            'items.product.category',
            'payments.createdBy',
        ]);

        return Inertia::render('Grn/Show', [
            'grn' => $grn,
        ]);
    }

    public function searchProduct(Request $request)
    {
        $query = $request->input('search', '');
        
        if (empty($query)) {
            return response()->json(['products' => []]);
        }

        $products = Product::with(['category', 'supplier'])
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json(['products' => $products]);
    }

    private function generateGrnNumber(): string
    {
        $year = now()->year;
        $lastGrn = Grn::whereYear('created_at', $year)->lockForUpdate()->latest('id')->first();
        $sequence = $lastGrn ? ((int) substr($lastGrn->grn_number, -4)) + 1 : 1;
        return 'GRN-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
