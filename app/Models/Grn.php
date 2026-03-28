<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_number',
        'supplier_id',
        'grn_date',
        'reference_no',
        'total_amount',
        'paid_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'grn_date' => 'date:Y-m-d',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculatePaymentStatus(): void
    {
        $paid = $this->payments()->sum('amount');
        $this->paid_amount = $paid;

        if ($paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paid >= $this->total_amount) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        $this->save();
    }
}
