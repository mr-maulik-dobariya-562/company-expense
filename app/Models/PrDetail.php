<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pr_details';

    protected $fillable = [
        'pr_id','date','vch_type','item_id','item_name','unit_name',
        'qty','qty_main_unit','qty_alt_unit','item_HSN_code','item_tax_category',
        'price','amount','net_amount','description','created_by'
    ];
}
