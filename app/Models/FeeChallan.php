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

        // Assign a fixed-length sequential challan number once the id is known.
        // Format: "1" + zero-padded id (e.g. 10000123). The leading 1 keeps new numbers
        // distinct from the old random 3-4 digit numbers even if a bank zero-pads them.
        static::created(function (FeeChallan $challan) {
            if (empty($challan->challan_no)) {
                $challan->challan_no = self::formatChallanNo($challan->id);
                $challan->saveQuietly();
            }
        });

        // Jab FeeChallan soft-delete
        static::deleting(function ($challan) {
            if (!$challan->isForceDeleting()) {
                $challan->payment()->delete();
            }
        });
    }

    public static function formatChallanNo(int $id): string
    {
        $length = max(4, (int) config('onelink.challan_no_length', 8));

        return '1' . str_pad((string) $id, $length - 1, '0', STR_PAD_LEFT);
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

    /**
     * The ID parents enter in their banking app (1Bill / 1LINK consumer number).
     */
    public function getConsumerNumberAttribute(): string
    {
        $prefix = GeneralSetting::first()?->one_bill_prefix ?? '';

        return $prefix . ($this->challan_no ?? $this->id);
    }

    public function isPaidViaOneLink(): bool
    {
        return $this->payment && $this->payment->source === ChallanPayment::SOURCE_ONELINK;
    }
}
