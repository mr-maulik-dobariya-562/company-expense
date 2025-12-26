<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleBillDetails extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'sale_id',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
