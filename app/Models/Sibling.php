<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sibling extends Model
{
    protected $fillable = [
        'student_id',
        'sibling_name',
        'sibling_class',
        'sibling_section',
        'sibling_in_dps',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
