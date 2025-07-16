<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\UserTrait;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, UserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $connection = 'transport';

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'password',
        'phone_number',
        'email',
        'nin',
        'next_of_kin_full_name',
        'email_verified',
        'verification_code',
        'status',
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

    public function tripBookings()
    {
        return $this->hasMany(TripBooking::class, 'user_id', 'id');
    }

    public function watchlists()
    {
        return $this->hasMany(WatchList::class, 'email', 'email');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function union()
    {
        return $this->hasOne(TransitCompanyUnion::class, 'id', 'transit_company_union_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    #[Scope]
    protected function scopeAgentsBetween(Builder $query, $from, $to): void
    {
        $query->whereNotNull('agent_id')
            ->whereBetween('created_at', [$from, $to]);
    }

    #[Scope]
    protected function scopeDriversBetween(Builder $query, $from, $to): void
    {
        $query->whereHas('vehicle')
            ->whereBetween('created_at', [$from, $to]);
    }

    #[Scope]
    protected function scopeIsAgent(Builder $query): void
    {
        $query->whereNotNull('agent_id');
    }

    #[Scope]
    protected function scopeSearch(Builder $query, string $keyword)
    {
        $query->where(fn ($q) => $q
            ->where('first_name', 'like', "%$keyword%")
            ->orWhere('last_name', 'like', "%$keyword%")
            ->orWhere('nin', $keyword)
            ->orWhere('id', $keyword)
        );
    }

    public function scopeFromWatchlist($query, WatchList $watchlist)
    {
        return $query->with(['tripBookings.trip' => fn($q) => $q->with(
                'transitCompany', 'departureState', 'departureCity', 
                'destinationState', 'destinationCity'
            )])
            ->where(function ($query) use ($watchlist) {
                $query->when($watchlist->nin, fn ($q) => $q->where('nin', $watchlist->nin))
                    ->when($watchlist->phone, fn ($q) => $q->orWhere('phone_number', $watchlist->phone))
                    ->when($watchlist->email, fn ($q) => $q->orWhere('email', $watchlist->email));
            });
    }

    public function scopeIsDriver(Builder $query){
        $query->whereHas('vehicle');
    }

    public static function booted()
    {
        static::addGlobalScope('zone', function(Builder $builder){
            if(app('tempStore')->has('zoneId')){
                $builder->where('zone_id', app('tempStore')->get('zoneId'));
            }
        });
    }
}
