<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'division',
        'parent_id',
        'img_url',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function supports()
    {
        return $this->hasMany(Support::class);
    }
}
