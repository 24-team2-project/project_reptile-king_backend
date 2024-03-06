<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'content',
        'img_urls',
        'answer',
        'answered_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
