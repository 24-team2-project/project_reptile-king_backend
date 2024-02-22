<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReptileSaleComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reptile_sale_id',
        'content',
        'group_comment_id',
        'parent_comment_id',
        'depth_no',
        'order_no',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reptileSale(){
        return $this->belongsTo(ReptileSale::class);
    }

}
