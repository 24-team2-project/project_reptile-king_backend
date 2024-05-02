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
        'delivery_fee',
        'category_id',
        'content',
        'img_urls',
        'created_at',
    ];

    protected $casts = [
        'img_urls' => 'json',
    ];

    protected $with = ['goodReviews'];

    public function goodReviews(){
        return $this->hasMany(GoodReview::class);
    }

    public function purchases(){
        return $this->hasMany(Purchase::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
