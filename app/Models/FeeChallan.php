<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeChallan extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'student_id', 'month','challan_no','include_admission_fee', 'status','issue_date',
        'due_date', 'total_amount' , 'amount_after_due_date' , 'note'
    ];

    protected static function boot()
    {
        parent::boot();
        // Jab FeeChallan soft-delete 
        static::deleting(function ($challan) {
            if (!$challan->isForceDeleting()) {
                $challan->payment()->delete();
            }
        });
    }

    protected $casts = [
        'include_admission_fee' => 'boolean',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeChallanItem::class, 'fee_challan_id');
    }

    public function payment()
    {
        return $this->hasOne(ChallanPayment::class);
    }
}
