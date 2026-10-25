<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ChallanPayment extends Model
{
    use SoftDeletes;

    public const SOURCE_MANUAL  = 'manual';
    public const SOURCE_ONELINK = 'onelink';

    protected $fillable = [
        'fee_challan_id',
        'bank_name',
        'paid_date',
        'transaction_id',
        'voucher_image',
        'status',
        'source',
        'tran_auth_id',
        'amount',
        'channel',
        'bank_mnemonic',
        'paid_at',
    ];

    protected $casts = [
        'paid_date' => 'date',
        'paid_at'   => 'datetime',
        'amount'    => 'decimal:2',
    ];

    public function challan()
    {
        return $this->belongsTo(FeeChallan::class,'fee_challan_id');
    }
}
