<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Grn;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;



class SupplierController extends Controller
{

    public function index()
    {
        $allsuppliers = Supplier::withCount('grns')
            ->withSum('grns', 'total_amount')
            ->withSum('payments', 'amount')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($supplier) {
                $supplier->total_purchases = (float) ($supplier->grns_sum_total_amount ?? 0);
                $supplier->total_paid      = (float) ($supplier->payments_sum_amount ?? 0);
                $supplier->outstanding     = $supplier->total_purchases - $supplier->total_paid;
                return $supplier;
            });

        return Inertia::render('Suppliers/Index', [
            'allsuppliers'   => $allsuppliers,
            'totalSuppliers' => $allsuppliers->count()
        ]);
    }

    // public function create()
    // {
    //     $categories = Category::all();

    //     return Inertia::render('Categories/Create', [
    //         'categories' => $categories,
    //     ]);
    // }

    private function generateSupplierCode(): string
    {
        $prefix = 'SUP-' . now()->format('Ymd') . '-';
        $last = Supplier::where('supplier_code', 'like', $prefix . '%')
            ->latest('id')
            ->value('supplier_code');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'supplier_code' => 'nullable|string|max:50|unique:suppliers,supplier_code',
            'name'    => 'required|string|max:191',
            'contact' => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255|unique:suppliers,email',
            'address' => 'nullable|string|max:500',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        $validated['supplier_code'] = $validated['supplier_code'] ?? $this->generateSupplierCode();



        // if ($request->hasFile('image')) {
        //     $fileExtension = $request->file('image')->getClientOriginalExtension();
        //     $fileName = 'supplier' . date("YmdHis") . '.' . $fileExtension;
        //     $destinationPath = "images/uploads/supplier/";
        //     $request->file('image')->move(public_path($destinationPath), $fileName);
        //     $validated['image'] = $destinationPath . $fileName;
        // }

        if ($request->hasFile('image')) {
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $fileName = 'supplier_' . date("YmdHis") . '.' . $fileExtension;
            $path = $request->file('image')->storeAs('suppliers', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        }

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->banner('Supplier created successfully.');
    }

    public function quickStore(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:191',
            'supplier_code' => 'nullable|string|max:50|unique:suppliers,supplier_code',
        ]);

        // Generate a unique placeholder email so the unique constraint is satisfied
        $validated['email']         = 'supplier_' . uniqid() . '@noemail.local';
        $validated['supplier_code'] = $validated['supplier_code'] ?? $this->generateSupplierCode();

        $supplier = Supplier::create($validated);

        return response()->json([
            'id'            => $supplier->id,
            'name'          => $supplier->name,
            'supplier_code' => $supplier->supplier_code,
        ]);
    }


    public function update(Request $request, Supplier $supplier)
    {

        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        // Validate incoming data
        $validated = $request->validate([
            'supplier_code' => 'nullable|string|max:50|unique:suppliers,supplier_code,' . $supplier->id,
            'name' => 'nullable|string|max:191',
            'contact' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($supplier->image && Storage::disk('public')->exists(str_replace('storage/', '', $supplier->image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $supplier->image));
            }

            // Save the new image
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $fileName = 'supplier_' . date("YmdHis") . '.' . $fileExtension;
            $path = $request->file('image')->storeAs('suppliers', $fileName, 'public');
            $validated['image'] = 'storage/' . $path;
        } else {
            // Retain the old image if no new image is uploaded
            $validated['image'] = $supplier->image;
        }


        $supplier->update($validated);


        // Redirect back with success message
        return redirect()->route('suppliers.index')->banner('Supplier updated successfully.');
    }





    public function destroy(Supplier $supplier)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        if ($supplier->image && Storage::disk('public')->exists(str_replace('storage/', '', $supplier->image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $supplier->image));
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->banner('Supplier deleted successfully.');
    }

    public function barcode(Supplier $supplier)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        return view('supplier-barcode', compact('supplier'));
    }
}
