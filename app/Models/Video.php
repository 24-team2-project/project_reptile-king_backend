<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'cage_id',
        'video_url',
        'created_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function cage(){
        return $this->belongsTo(Cage::class);
    }
}
