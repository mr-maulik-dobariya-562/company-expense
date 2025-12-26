<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrRevisionDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pr_revision_details';
    protected $fillable = ['pr_revision_id', 'item_id', 'item_name', 'qty', 'price', 'amount', 'net_amount', 'description', 'created_by'];
}

