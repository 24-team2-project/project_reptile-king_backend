<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'good_id',
        'summary',
        'content',
        'stars',
        'img_urls',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function good(){
        return $this->belongsTo(User::class);
    }


}
