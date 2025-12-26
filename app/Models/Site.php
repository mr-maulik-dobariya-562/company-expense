<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        "name",
        "work_category_id",
        "created_by",
        "updated_by",
        'email_enable'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function buyer()
    {
        return $this->hasMany(Buyer::class, "site_id");
    }

    public function workCategory()
    {
        return $this->belongsTo(WorkCategory::class, "work_category_id");
    }
}
