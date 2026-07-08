<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $fillable = ['student_id','guardian_name','relation','guardian_nic','contact_no'];
}
