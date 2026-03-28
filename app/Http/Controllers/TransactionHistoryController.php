<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product; 
use App\Models\CompanyInfo;
use Illuminate\Support\Facades\Gate;

class TransactionHistoryController extends Controller
{
    public function index()
{
    $allhistoryTransactions = Sale::with(['saleItems','saleItems.product','customer','user'])
        ->orderBy('created_at', 'desc')
        ->get();

    $companyInfo = CompanyInfo::all();

    return Inertia::render('TransactionHistory/Index', [
        'allhistoryTransactions' => $allhistoryTransactions,
        'totalhistoryTransactions' => $allhistoryTransactions->count(),
        'companyInfo' => $companyInfo,
    ]);
}

public function destroy(Request $request)
{
    $request->validate([
        'order_id' => 'required|string|exists:sales,order_id',
    ]);

    $sale = Sale::where('order_id', $request->order_id)->first();

    if (!$sale) {
        return response()->json(['message' => 'Sale not found'], 404);
    }

    // Retrieve sale items and restore product stock
    foreach ($sale->saleItems as $saleItem) {
        $product = Product::find($saleItem->product_id);
        if ($product) {
            $product->increment('stock_quantity', $saleItem->quantity);
        }
    }

    // Delete associated sale items
    SaleItem::where('sale_id', $sale->id)->delete();

    // Delete the sale record
    $sale->delete();

    return redirect()->route('transactionHistory.index')->banner('Record deleted successfully, and stock updated.');
}


}
