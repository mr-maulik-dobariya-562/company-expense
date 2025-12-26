<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pr extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pr';

    protected $fillable = [
        'product_name',
        'status',
        'is_pr_status',
        'boq_id',
        'party_id',
        'document',
        'date',
        'name_1',
        'name_2',
        'type',
        'new_type',
        'vch_no',
        'remark',
        'rejection_reason',
        'created_by',
        'updated_by',
        'is_po_status',
        'po_remark'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function boq()
    {
        return $this->belongsTo(Boq::class);
    }

    public function details()
    {
        return $this->hasMany(PrDetail::class);
    }

    public function billDetails()
    {
        return $this->hasMany(PrBillDetail::class);
    }

    public function revisions()
    {
        return $this->hasOne(PrRevision::class, 'pr_id', 'id');
    }
}
