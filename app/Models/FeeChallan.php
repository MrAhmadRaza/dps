<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeChallan extends Model
{
    protected $fillable = [
        'student_id', 'voucher_id', 'month', 'status'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function voucher() {
        return $this->belongsTo(Voucher::class);
    }

    public function payment()
    {
        return $this->hasOne(ChallanPayment::class);
    }
}
