<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ShiftController extends Controller
{
    /** List all shifts — Admin/Manager only */
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $shifts = Shift::with('user')
            ->when($request->search, fn($q) => $q->where('shift_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Shifts/Index', [
            'shifts'  => $shifts,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    /** Show the "Open Shift" form */
    public function open()
    {
        $existing = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route('pos.index');
        }

        return Inertia::render('Shifts/Open');
    }

    /** Start a new shift */
    public function start(Request $request)
    {
        $request->validate([
            'opening_float' => 'required|numeric|min:0',
            'notes'         => 'nullable|string|max:500',
        ]);

        $existing = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return redirect()->route('pos.index');
        }

        $shiftNumber = $this->generateShiftNumber();

        Shift::create([
            'user_id'        => Auth::id(),
            'shift_number'   => $shiftNumber,
            'start_time'     => now(),
            'opening_float'  => $request->opening_float,
            'cash_in_drawer' => $request->opening_float,
            'status'         => 'open',
            'notes'          => $request->notes,
        ]);

        return redirect()->route('pos.index')
            ->banner("Shift {$shiftNumber} opened. Have a great shift!");
    }

    /** Show close-shift confirmation page */
    public function closeForm(Shift $shift)
    {
        if ($shift->user_id !== Auth::id() && !Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        if ($shift->status === 'closed') {
            return redirect()->back()->dangerBanner('This shift is already closed.');
        }

        $totalSales = Sale::whereBetween('created_at', [$shift->start_time, now()])
            ->where('user_id', $shift->user_id)
            ->sum('total_amount');

        $shift->load('user');

        return Inertia::render('Shifts/Close', [
            'shift'      => $shift,
            'totalSales' => (float) $totalSales,
        ]);
    }

    /** Close the shift */
    public function close(Request $request, Shift $shift)
    {
        if ($shift->user_id !== Auth::id() && !Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        if ($shift->status === 'closed') {
            return redirect()->back()->dangerBanner('This shift is already closed.');
        }

        $request->validate([
            'closing_float' => 'required|numeric|min:0',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $totalSales = Sale::whereBetween('created_at', [$shift->start_time, now()])
            ->where('user_id', $shift->user_id)
            ->sum('total_amount');

        $shift->update([
            'end_time'       => now(),
            'closing_float'  => $request->closing_float,
            'total_sales'    => $totalSales,
            'cash_in_drawer' => $request->closing_float,
            'status'         => 'closed',
            'notes'          => $request->notes ?? $shift->notes,
        ]);

        // Cashiers go back to open page; admin/manager go to index
        if (Gate::allows('hasRole', ['Admin', 'Manager'])) {
            return redirect()->route('shifts.index')
                ->banner("Shift {$shift->shift_number} closed successfully.");
        }

        return redirect()->route('shifts.open')
            ->banner("Shift {$shift->shift_number} closed. Total sales: Rs. " . number_format($totalSales, 2));
    }

    /** JSON: return the current user's active shift (used by POS page) */
    public function current()
    {
        $shift = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        return response()->json(['shift' => $shift]);
    }

    private function generateShiftNumber(): string
    {
        $year   = now()->year;
        $prefix = 'SHF-' . $year . '-';
        $last   = Shift::where('shift_number', 'like', $prefix . '%')
            ->latest('id')
            ->value('shift_number');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}

