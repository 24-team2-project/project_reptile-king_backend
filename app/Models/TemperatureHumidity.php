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

    protected $casts = [
        'temperature' => 'float',
        'humidity' => 'float',
        // 'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function cage(){
        return $this->belongsTo(Cage::class);
    }

}
