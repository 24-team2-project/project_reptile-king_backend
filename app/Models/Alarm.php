<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'category',
        'title',
        'content',
        'readed',
        'img_urls',
        'created_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }


}
