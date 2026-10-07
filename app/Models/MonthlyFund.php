<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyFund extends Model
{
    protected $fillable = [
        'fund_date',
        'month_date',
        'amount',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fund_date' => 'date',
            'month_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
