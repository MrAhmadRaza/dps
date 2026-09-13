<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Parant extends Authenticatable
{
    protected $table = 'parents';
    protected $fillable = [
        'father_name','father_nic', 'password' , 'mother_name','occupation','income',
        'contact_no','address',
    ];

    // Password
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function students()
    {
        return $this->hasMany(Student::class,'parent_id');
    }
}
