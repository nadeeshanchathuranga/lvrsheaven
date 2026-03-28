<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $supplierId = $request->input('supplier_id');

        $payments = SupplierPayment::with('supplier', 'grn', 'createdBy')
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->orderBy('payment_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Suppliers/Payments', [
            'payments' => $payments,
            'suppliers' => $suppliers,
            'filters' => $request->only(['supplier_id']),
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'grn_id'         => 'nullable|exists:grns,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,other',
            'notes'          => 'nullable|string|max:500',
        ]);

        // If a GRN is specified, validate amount doesn't exceed outstanding on that GRN
        if (!empty($validated['grn_id'])) {
            $grn = Grn::findOrFail($validated['grn_id']);
            $outstanding = $grn->total_amount - $grn->paid_amount;
            if ($validated['amount'] > $outstanding + 0.001) {
                return back()->withErrors(['amount' => 'Payment amount exceeds the outstanding balance of ' . number_format($outstanding, 2) . ' for this GRN.']);
            }
        }

        $payment = SupplierPayment::create([
            'supplier_id'    => $validated['supplier_id'],
            'grn_id'         => $validated['grn_id'] ?? null,
            'amount'         => $validated['amount'],
            'payment_date'   => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        // Update GRN payment status if linked
        if (!empty($validated['grn_id'])) {
            $payment->grn->recalculatePaymentStatus();
        }

        return back()->banner('Payment recorded successfully.');
    }

    public function destroy(SupplierPayment $supplierPayment)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $grn = $supplierPayment->grn;
        $supplierPayment->delete();

        if ($grn) {
            $grn->recalculatePaymentStatus();
        }

        return back()->banner('Payment deleted successfully.');
    }
}
