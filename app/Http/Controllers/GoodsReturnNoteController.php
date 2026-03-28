<?php

namespace App\Http\Controllers;

use App\Models\GoodsReturnNote;
use App\Models\GoodsReturnNoteItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GoodsReturnNoteController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $query    = $request->input('search');
        $supplierId = $request->input('supplier_id');
        $from     = $request->input('from');
        $to       = $request->input('to');

        $returns = GoodsReturnNote::with('supplier', 'createdBy')
            ->withCount('items')
            ->when($query, fn($q) => $q->where('grn_number', 'like', "%{$query}%")
                ->orWhere('reference_no', 'like', "%{$query}%"))
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->when($from, fn($q) => $q->whereDate('return_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('return_date', '<=', $to))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $totals = [
            'total_returns'      => GoodsReturnNote::count(),
            'total_value'        => (float) GoodsReturnNote::sum('total_amount'),
        ];

        return Inertia::render('GoodsReturnNote/Index', [
            'returns'   => $returns,
            'suppliers' => $suppliers,
            'totals'    => $totals,
            'filters'   => $request->only('search', 'supplier_id', 'from', 'to'),
        ]);
    }

    public function create()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return Inertia::render('GoodsReturnNote/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'return_date'  => 'required|date',
            'items'        => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $grnNumber = $this->generateGrnNumber();
            $totalAmount = collect($request->items)
                ->sum(fn($i) => $i['quantity'] * $i['unit_cost']);

            $grn = GoodsReturnNote::create([
                'grn_number'   => $grnNumber,
                'supplier_id'  => $request->supplier_id ?: null,
                'return_date'  => $request->return_date,
                'reference_no' => $request->reference_no,
                'reason'       => $request->reason ?? 'other',
                'total_amount' => $totalAmount,
                'notes'        => $request->notes,
                'created_by'   => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $lineCost = $item['quantity'] * $item['unit_cost'];

                GoodsReturnNoteItem::create([
                    'goods_return_note_id' => $grn->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_cost'  => $item['unit_cost'],
                    'total_cost' => $lineCost,
                    'notes'      => $item['notes'] ?? null,
                ]);

                // Deduct stock
                $product = Product::findOrFail($item['product_id']);
                $product->decrement('stock_quantity', $item['quantity']);
            }
        });

        return redirect()->route('goods-return-notes.index')
            ->banner('Goods Return Note created successfully.');
    }

    public function show(GoodsReturnNote $goodsReturnNote)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $goodsReturnNote->load('supplier', 'items.product', 'createdBy');

        return Inertia::render('GoodsReturnNote/Show', [
            'returnNote' => $goodsReturnNote,
        ]);
    }

    private function generateGrnNumber(): string
    {
        $year   = date('Y');
        $prefix = 'GRN-RET-' . $year . '-';
        $last   = GoodsReturnNote::where('grn_number', 'like', $prefix . '%')
            ->orderByDesc('grn_number')
            ->value('grn_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
