<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReturnNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_return_note_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'float',
        'total_cost' => 'float',
    ];

    public function goodsReturnNote()
    {
        return $this->belongsTo(GoodsReturnNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
