<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemperatureHumidity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'serial_code',
        'temperature',
        'humidity',
        'created_at',
    ];

    public function cage(){
        return $this->belongsTo(Cage::class);
    }

}
