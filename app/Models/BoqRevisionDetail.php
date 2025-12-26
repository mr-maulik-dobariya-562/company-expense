<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoqRevisionDetail extends Model
{
    use HasFactory;

    protected $table = 'boq_revision_details';

    protected $fillable = [
        'boq_revision_id',
        'item_id',
        'item_name',
        'qty',
        'created_by',
    ];

    public function revision()
    {
        return $this->belongsTo(BoqRevision::class, 'boq_revision_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
