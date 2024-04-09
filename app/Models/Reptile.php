<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reptile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'serial_code', 
        'species',
        'gender',
        'birth',
        'nickname',
        'memo',
        'img_urls',
        'expired_at'
    ];

    protected $casts = [
        'img_urls' => 'array',
        'birth' => 'date'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function cage(){
        return $this->belongsTo(Cage::class);
    }
    
    public function moultingCycles(){
        return $this->hasMany(MoultingCycle::class);
    }

}
