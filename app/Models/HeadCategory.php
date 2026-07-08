<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadCategory extends Model
{
    protected $fillable = [
        'category_id','date','bill_no','head_name','amount',
    ];

    public function categories()
    {
        return $this->belongsTo(Category::class , 'category_id');
    }
}
