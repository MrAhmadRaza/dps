<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ChallanPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fee_challan_id',
        'bank_name',
        'paid_date',
        'transaction_id',
        'voucher_image',
        'status',
    ];

    protected $casts = [
        'paid_date' => 'date',
    ];

    public function challan()
    {
        return $this->belongsTo(FeeChallan::class,'fee_challan_id');
    }
}
