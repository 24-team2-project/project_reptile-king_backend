<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAuthCode extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'email',
        'auth_code',
        'created_at',
        'expired_at'
    ];


}
