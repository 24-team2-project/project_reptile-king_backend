<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id',
        'zipcode',
        'address',
        'detail_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
