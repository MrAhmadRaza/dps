<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FeeChallanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_challan_id','fee_item_id','fee_item_name','amount'
    ];

    public function feeChallan(): BelongsTo
    {
        return $this->belongsTo(FeeChallan::class, 'fee_challan_id');
    }

    /**
     * Original Fee Item / Voucher Item
     */
    public function feeItem(): BelongsTo
    {
        return $this->belongsTo(VoucherItem::class, 'fee_item_id')->withDefault(function ($voucherItem, $feeChallanItem) {
            $voucherItem->fee_name = $feeChallanItem->fee_item_name;
        });
    }
}
