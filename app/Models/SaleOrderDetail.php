<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrderDetail extends Model
{
	use SoftDeletes, HasFactory;

	protected $fillable = [
		'sale_order_id',
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

	public function sale()
	{
		return $this->hasMany(Sale::class, 'sale_order_id');
	}

	public function saleOrders()
	{
		return $this->belongsTo(SaleOrder::class, 'sale_order_id');
	}

	// public function buyerSingles()
	// {
	//     return $this->belongsTo(BuyerSingle::class, "buyer_id");
	// } 

}
