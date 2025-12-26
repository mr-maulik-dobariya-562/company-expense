<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseBillDetail extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'purchase_id',
        'bs_name',
        'percent_val',
        'percent_operated_on',
        'amount',
        'date',
        'vch_no',
        'type',
        'tmp_vch_code',
        'tmp_bs_code',
        'created_by',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
