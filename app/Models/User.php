<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $connection = 'transport';

    protected $fillable = [
        "uuid",
        "first_name",
        "last_name",
        'password',
        "phone_number",
        'email',
        "nin",
        "next_of_kin_full_name",
        "email_verified",
        "verification_code",
        "status",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tripBookings(){
        return $this->hasMany(TripBooking::class, 'user_id', 'id');
    }

    public function watchlists(){
        return $this->hasMany(WatchList::class, 'email', 'email');
    }

    public function vehicle(){
        return $this->hasOne(Vehicle::class);
    }

    public function documents(){
        return $this->hasMany(Document::class);
    }

    public function union(){
        return $this->hasOne(TransitCompanyUnion::class, 'id', 'transit_company_union_id');
    }

    public function trips(){
        return $this->hasMany(Trip::class);
    }
}
