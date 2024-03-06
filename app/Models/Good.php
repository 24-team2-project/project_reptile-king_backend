<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Good extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'price',
        'category',
        'content',
        'img_urls',
        'created_at',
    ];

    protected $with = ['goodReviews'];

    public function goodReviews(){
        return $this->hasMany(GoodReview::class);
    }

    public function purchases(){
        return $this->hasMany(Purchase::class);
    }
    
}
