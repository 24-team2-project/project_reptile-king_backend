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
        'serial_code',
        'video_url',
        'created_at',
    ];

    public function cage(){
        return $this->belongsTo(Cage::class);
    }
}
