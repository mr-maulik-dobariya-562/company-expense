<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyerSingle extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;
    protected $fillable = [
        'id',
        "name",
        "created_by",
        "updated_by"
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }
}
