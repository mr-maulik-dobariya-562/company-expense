<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailContent extends Model
{
    use HasFactory;
    protected $fillable = [
        'email_history_id',
        'payment_email_id',
        'thread_id',
        'email_content',
        'reminder_count'
    ];

    public function emailHistory()
    {
        return $this->belongsTo(EmailHistory::class);
    }
}
