<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class IPAddress extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    protected $table = "ip_addresses";
    protected $fillable = [
        "name",
        "created_by",
        "updated_by"
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }
}
