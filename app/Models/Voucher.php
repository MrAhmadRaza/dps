<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'academic_session_id',
        'session_item_id',
        'voucher_no',
        'type',              
        'month',
        'due_date',
        'total_amount',
        'notes'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];
    
    //  Relations
    public function student()
    {
        return Student::where('academic_session_id', $this->academic_session_id)
            ->where('session_item_id', $this->session_item_id)
            ->first();
    }
    // public function student(){return $this->belongsTo(Student::class,'academic_session_id');}
    public function academicSession(){return $this->belongsTo(AcademicSession::class,'academic_session_id');}
    public function sessionItem(){return $this->belongsTo(SessionItem::class,'session_item_id');}
    public function items(){return $this->hasMany(VoucherItem::class);}

}
