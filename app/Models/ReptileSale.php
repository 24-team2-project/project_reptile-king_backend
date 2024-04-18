<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReptileSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'price',
        'priority_area',
        'img_urls',
        'sold_out',
    ];

    protected $with = ['reptileSaleComments'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reptileSaleComments(){
        return $this->hasMany(ReptileSaleComment::class);
    }
    


}
