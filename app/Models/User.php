<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nickname',
        'address',
        'phone',
        'payment_selection',
        'img_urls',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'address' => 'json',
        'payment_selection' => 'json',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims() // payload에 추가로 저장시킬 사항들
    {
        return [];
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function supports(){
        return $this->hasMany(Support::class);
    }

    public function purchases(){
        return $this->hasMany(Purchase::class);
    }

    public function goodReviews(){
        return $this->hasMany(GoodReview::class);
    }

    public function reptileSales(){
        return $this->hasMany(ReptileSale::class);
    }

    public function reptileSaleComments(){
        return $this->hasMany(ReptileSaleComment::class);
    }

    public function reptiles(){
        return $this->hasMany(Reptile::class);
    }

    public function cages(){
        return $this->hasMany(Cage::class);
    }

    public function movements(){
        return $this->hasMany(Movement::class);
    }

    public function moultingCycles(){
        return $this->hasMany(MoultingCycle::class);
    }

    public function alarms(){
        return $this->hasMany(Alarm::class);
    }

    public function checklists(){
        return $this->hasMany(Checklist::class);
    }

    public function roles(){
        return $this->belongsToMany(Role::class)->withPivot('created_at');
    }

}
