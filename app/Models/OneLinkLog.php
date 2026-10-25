<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OneLinkLog extends Model
{
    protected $table = 'onelink_logs';

    protected $fillable = [
        'type', 'consumer_number', 'tran_auth_id', 'amount', 'response_code',
        'fee_challan_id', 'ip', 'request', 'response',
    ];

    protected $casts = [
        'request'  => 'array',
        'response' => 'array',
    ];

    public function challan()
    {
        return $this->belongsTo(FeeChallan::class, 'fee_challan_id');
    }
}
