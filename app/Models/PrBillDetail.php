<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrBillDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pr_bill_details';

    protected $fillable = [
        'pr_id','bs_name','percent_val','percent_operated_on','amount',
        'date','vch_no','type','tmp_vch_code','tmp_bs_code','created_by'
    ];
}
