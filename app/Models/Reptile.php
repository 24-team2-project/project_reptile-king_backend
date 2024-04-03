<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reptile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'species',
        'gender',
        'age',
        'nickname',
        'memo',
        'expired_at'
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
