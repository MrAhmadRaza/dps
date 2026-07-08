<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    protected $fillable = ['session_name','start_date','end_date','status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function sessionItems()
    {
        return $this->hasMany(SessionItem::class);
    }
}
