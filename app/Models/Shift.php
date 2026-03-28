<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_number',
        'start_time',
        'end_time',
        'opening_float',
        'closing_float',
        'total_sales',
        'cash_in_drawer',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time'    => 'datetime',
        'end_time'      => 'datetime',
        'opening_float' => 'float',
        'closing_float' => 'float',
        'total_sales'   => 'float',
        'cash_in_drawer' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
