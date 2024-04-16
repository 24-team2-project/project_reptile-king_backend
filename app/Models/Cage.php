<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'reptile_serial_code',
        'memo',
        'set_temp',
        'set_hum',
        'serial_code',
        'img_urls',
        'expired_at',
    ];

    protected $casts = [
        'img_urls' => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reptile(){
        return $this->belongsTo(Reptile::class);
    }

    public function temperatureHumiditys(){
        return $this->hasMany(TemperatureHumidity::class);
    }

    public function movements(){
        return $this->hasMany(Movement::class);
    }

    public function videos(){
        return $this->hasMany(Video::class);
    }

}
