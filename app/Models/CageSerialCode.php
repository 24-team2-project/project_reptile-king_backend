<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CageSerialCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'size',
        'serial_code',
        'created_at',
        'updated_at',
    ];

}
