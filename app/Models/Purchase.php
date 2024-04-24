<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'good_id',
        'total_price',
        'quantity',
        'payment_selection',
    ];

    public function good(){
        return $this->belongsTo(Good::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
