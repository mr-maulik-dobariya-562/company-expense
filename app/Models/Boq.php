<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boq extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "boq";

    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'sale_order_id',
        'remark',
        'pr_remark',
        'ref_no',
        'status',
        'is_boq_status',
        'is_pr_status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function boqDetails()
    {
        return $this->hasMany(BoqDetail::class);
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function revisions()
    {
        return $this->hasMany(BoqRevision::class);
    }

}
