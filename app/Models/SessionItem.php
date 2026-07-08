<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionItem extends Model
{
    protected $fillable = [
        'academic_session_id','section','class',
    ];

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }
   
}
