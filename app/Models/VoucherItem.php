<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherItem extends Model
{

    protected $fillable = [
       'voucher_id',
       'fee_name',
       'amount',
    ];
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
