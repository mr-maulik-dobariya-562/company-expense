<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailThread extends Model
{
    use HasFactory;

    protected $fillable = ['recipients', 'thread_id'];

    protected $casts = [
        'recipients' => 'array', // Auto-convert JSON to array
    ];
}
