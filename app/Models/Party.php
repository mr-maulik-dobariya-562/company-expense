<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use SoftDeletes, LogsActivity, HasFactory;

    protected $table = "partys";

    protected $fillable = [
        "name",
        "created_by"
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }
}
