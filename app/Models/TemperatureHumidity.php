<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemperatureHumidity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'cage_id',
        'temperature',
        'humidity',
        'created_at',
    ];

    public function cage(){
        return $this->belongsTo(Cage::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
