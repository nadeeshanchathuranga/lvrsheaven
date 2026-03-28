<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
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

        return Inertia::render('Grn/Create', [
            'suppliers' => $suppliers,
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
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_no'  => 'nullable|string|max:100',
            'items.*.expire_date' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($validated) {
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
                $lineTotal = $item['unit_cost'] * $item['quantity'];

                GrnItem::create([
                    'grn_id'      => $grn->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_cost'   => $item['unit_cost'],
                    'total_cost'  => $lineTotal,
                    'batch_no'    => $item['batch_no'] ?? null,
                    'expire_date' => $item['expire_date'] ?? null,
                ]);

                $product = Product::find($item['product_id']);
                $product->stock_quantity += $item['quantity'];
                $product->total_quantity = ($product->total_quantity ?? 0) + $item['quantity'];
                if (!empty($item['batch_no'])) {
                    $product->batch_no = $item['batch_no'];
                }
                if (!empty($item['expire_date'])) {
                    $product->expire_date = $item['expire_date'];
                }
                $product->cost_price = $item['unit_cost'];
                $product->purchase_date = $validated['grn_date'];
                $product->save();

                StockTransaction::create([
                    'product_id'       => $product->id,
                    'transaction_type' => 'GRN',
                    'quantity'         => $item['quantity'],
                    'transaction_date' => $validated['grn_date'],
                    'supplier_id'      => $validated['supplier_id'],
                    'reason'           => 'GRN: ' . $grnNumber,
                ]);
            }

            return redirect()->route('grn.show', $grn->id)
                ->banner('GRN ' . $grnNumber . ' created successfully.');
        });
    }

    public function show(Grn $grn)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $grn->load([
            'supplier',
            'createdBy',
            'items.product',
            'payments.createdBy',
        ]);

        return Inertia::render('Grn/Show', [
            'grn' => $grn,
        ]);
    }

    private function generateGrnNumber(): string
    {
        $year = now()->year;
        $lastGrn = Grn::whereYear('created_at', $year)->lockForUpdate()->latest('id')->first();
        $sequence = $lastGrn ? ((int) substr($lastGrn->grn_number, -4)) + 1 : 1;
        return 'GRN-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
