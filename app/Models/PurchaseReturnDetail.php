<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnDetail extends Model
{
    use SoftDeletes, HasFactory;

	protected $fillable = [
		'purchase_return_id',
		'date',
		'vch_type',
        'item_id',
		'item_name',
		'unit_name',
		'qty',
		'qty_main_unit',
		'qty_alt_unit',
		'item_HSN_code',
		'item_tax_category',
		'price',
		'amount',
		'net_amount',
		'description',
		'created_by',
	];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }
}
