<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
    ];

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

    public function ReptileSaleComments(){
        return $this->hasMany(ReptileSaleComment::class);
    }

    public function reptiles(){
        return $this->hasMany(Reptile::class);
    }

    public function cages(){
        return $this->hasMany(Cage::class);
    }

    public function tmeperatureHumiditys(){
        return $this->hasMany(TemperatureHumidity::class);
    }

    public function Movements(){
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
        return $this->BelongsToMany(Role::class)->withTimestamps();
    }

}
