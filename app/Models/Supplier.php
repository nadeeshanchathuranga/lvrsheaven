<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Supplier $supplier) {
            if (!empty(trim((string) $supplier->supplier_code))) {
                return;
            }

            $supplier->updateQuietly([
                'supplier_code' => str_pad((string) $supplier->id, 4, '0', STR_PAD_LEFT),
            ]);
        });

        static::updating(function (Supplier $supplier) {
            if (!empty(trim((string) $supplier->supplier_code))) {
                return;
            }

            $supplier->supplier_code = str_pad((string) $supplier->id, 4, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = [
        'supplier_code',
        'name',
        'contact',
        'email',
        'address',
        'image',
    ];

    public function grns()
    {
        return $this->hasMany(Grn::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getTotalPurchasesAttribute(): float
    {
        return (float) $this->grns()->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->total_purchases - $this->total_paid;
    }
}
