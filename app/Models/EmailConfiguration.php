<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConfiguration extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        "email",
        "password",
        "port_no",
        "host",
        "mailer",
        "encryption",
        "mrn_reminder_day",
        "payment_reminder_day",
        "created_by",
        'email_enable'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }
}
