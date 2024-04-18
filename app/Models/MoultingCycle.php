<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoultingCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reptile_id',
        'date',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reptile(){
        return $this->belongsTo(Reptile::class);
    }


}
