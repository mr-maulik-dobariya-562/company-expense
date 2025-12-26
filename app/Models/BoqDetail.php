<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoqDetail extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "boq_detail";
    
    protected $fillable = [
        "boq_id",
        "item_id",
        "item_name",
        "qty",
        "created_by"
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function boq()
    {
        return $this->belongsTo(Boq::class);
    }
}
