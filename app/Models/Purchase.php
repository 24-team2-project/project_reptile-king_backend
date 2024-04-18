<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'good_id',
        'total_price',
        'quantity',
        'payment_selection',
        'created_at',
    ];

    public function good(){
        return $this->belongsTo(Good::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
