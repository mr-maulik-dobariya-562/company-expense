<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockDetail extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'date',
        'vch_type',
        'stock_id',
        'item_name',
        'unit_name',
        'qty',
        'price',
        'amount',
        'created_by',
    ];
}
