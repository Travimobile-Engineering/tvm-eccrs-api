<?php

namespace App\Models;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AuthUser extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $connection = 'authuser';

    protected $table = 'users';

    protected $fillable = [
        'uuid',
        'unique_id',
        'first_name',
        'last_name',
        'sms_verified',
        'user_category',
        'wallet',
        'txn_pin',
        'address',
        'gender',
        'is_admin',
        'nin',
        'next_of_kin_full_name',
        'next_of_kin_phone_number',
        'next_of_kin_gender',
        'next_of_kin_relationship',
        'verification_code',
        'verification_code_expires_at',
        'email_verified_at',
        'custom_fields',
        'avatar_url',
        'uuid',
        'phone_number',
        'email',
        'email_verified',
        'password',
        'transit_company_union_id',
        'profile_photo',
        'public_id',
        'driver_verified',
        'agent_id',
        'is_available',
        'lng',
        'lat',
        'trip_extended_time',
        'inbox_notifications',
        'email_notifications',
        'status',
        'reason',
        'security_question_id',
        'security_answer',
        'fcm_token',
        'is_premium_driver',
        'reset_code',
        'reset_code_expires_at',
        'organization_id',
        'zone_id',
        'state_id',
        'platform',
        'suspended_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'verification_code_expires_at',
        'reset_code',
        'reset_code_expires_at',
        'email_verified_at',
        'is_admin',
        'created_at',
        'updated_at',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($user): void {
            $user->uuid = Str::uuid();
        });
        // static::bootDeletesUserRelationships();
    }

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
            'driver_verified' => 'boolean',
            'is_available' => 'boolean',
            'inbox_notifications' => 'boolean',
            'email_notifications' => 'boolean',
            'status' => UserStatus::class,
            'is_premium_driver' => 'boolean',
            'email_verified' => 'boolean',
            'suspended_until' => 'datetime',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function zoneModel()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function stateModel()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function suspensions()
    {
        return $this->hasMany(Suspension::class, 'user_id');
    }

    public function latestSuspension()
    {
        return $this->hasOne(Suspension::class, 'user_id')->latestOfMany();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', fn ($q) => $q->where('name', $permission)
        )->exists();
    }

    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching($role);
    }

    public function syncRoles(array $roles): void
    {
        $this->roles()->sync($roles);
    }

    public function role(): Attribute
    {
        return Attribute::get(
            fn () => $this->roles()->first()
        );
    }

    public function zone(): Attribute
    {
        return Attribute::get(
            fn () => Zone::find($this->zone_id)
        );
    }

    public function state(): Attribute
    {
        return Attribute::get(
            fn () => State::find($this->state_id)
        );
    }
}
