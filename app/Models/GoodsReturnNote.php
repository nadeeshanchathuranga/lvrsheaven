<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReturnNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_number',
        'supplier_id',
        'return_date',
        'reference_no',
        'reason',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date:Y-m-d',
        'total_amount' => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReturnNoteItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
