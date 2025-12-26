<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->created_by = auth()->user()->id;
        });
    }

    protected $fillable = [
        "name",
        "created_by"
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }
}
