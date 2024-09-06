<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'phone',
        'city',
        'role',
        'status',
        'image',
        'company_id',
        'is_owner',
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
        'email_verified_at' => 'datetime',
    ];

    // apend value 
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/profile_images/' . $this->image);
        } else {
            return $this->images[0]->image_url ?? null;
        }
    }

    // company 
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Message 
    public function sendMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Message 
    public function receiveMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
