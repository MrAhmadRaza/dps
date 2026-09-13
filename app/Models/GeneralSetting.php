<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = [
       'name',
       'account_no',
       'one_bill_prefix',
       'late_fee_fine',
    ];
}
