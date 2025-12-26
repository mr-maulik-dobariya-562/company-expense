<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrRevisionBillDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pr_revision_bill_details';
    protected $fillable = ['pr_revision_id', 'bs_name', 'percent_val', 'percent_operated_on', 'amount', 'created_by'];
}
