<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buyer extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        "buyer_single_id",
        "site_id",
        "created_by"
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function buyerSingles()
    {
        return $this->belongsTo(BuyerSingle::class, "buyer_single_id");
    }
}
