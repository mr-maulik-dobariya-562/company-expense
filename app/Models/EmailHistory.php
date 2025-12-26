<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'thread_id', 'sale_ids', 'emails', 'cc_emails'
    ];

    public function getSaleIdsAttribute($value)
    {
        return explode(',', $value);
    }

    public function getEmailsAttribute($value)
    {
        return explode(',', $value);
    }

    public function emailContents()
    {
        return $this->hasMany(EmailContent::class);
    }
}
