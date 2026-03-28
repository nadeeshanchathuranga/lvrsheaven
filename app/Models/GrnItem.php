<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'batch_no',
        'expire_date',
    ];

    protected $casts = [
        'expire_date' => 'date:Y-m-d',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
