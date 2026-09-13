<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'parent_id','register_no','name','date_of_birth','b_form_no','religion','gender','caste','domicile',
        'academic_session_id','session_item_id','discount','discount_amount','discount_due_date','discount_notes','previous_school',
        'fees_paid_last_institution','last_fee_paid_upto','games_sports','extra_curricular','photo_path','admission_date',
        'created_by','receipt_no','fee_paid_date','status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_fee_paid_upto' => 'date',
        'admission_date' => 'date',
        'fee_paid_date'=> 'date',
    ];

    public function parent(){ return $this->belongsTo(Parant::class); }
    public function guardian(){ return $this->hasOne(Guardian::class); }
    public function sibling(){ return $this->hasOne(Sibling::class); }
    public function academicSession(){return $this->belongsTo(AcademicSession::class,'academic_session_id');}
    public function sessionItem(){return $this->belongsTo(SessionItem::class,'session_item_id');}
    public function challans(){return $this->hasMany(FeeChallan::class, 'student_id');}
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
