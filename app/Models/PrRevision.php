<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrRevision extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pr_revisions';
    protected $fillable = [
        'pr_id',
        'version',
        'status',
        'remark',
        'party_id',
        'boq_id',
        'date',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'deleted_at',
    ];

    public function pr()
    {
        return $this->belongsTo(Pr::class, 'pr_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(PrRevisionDetail::class, 'pr_revision_id');
    }

    public function billDetails()
    {
        return $this->hasMany(PrRevisionBillDetail::class, 'pr_revision_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
