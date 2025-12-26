<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_activity';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'url',
        'method',
        'ip',
        'agent',
        'vch_no',
        'table_id',
        'user_id'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ipAddress()
    {
        return $this->belongsTo(IPAddress::class, 'ip_address_id');
    }
}
