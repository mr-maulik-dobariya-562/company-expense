<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materials extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'product_name',
        'ref_no',
        'date',
        'party_id',
        'name_1',
        'name_2',
        'type',
        'status',
        'buyer_id',
        'sale_order_id',
        'vch_No',
        'store_phone',
        'store_email',
        'store_cc_email',
        'purchase_phone',
        'purchase_email',
        'purchase_cc_email',
        'lead_delivery_date',
        'amount',
        'mrn_date',
        'mrn_no',
        'mrn_image',
        'credited_days',
        'payment_checkbox',
        'created_at',
        'created_by',
    ];
}
