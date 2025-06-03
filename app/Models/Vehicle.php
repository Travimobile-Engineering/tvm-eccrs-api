<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    protected $fillable = [
        "name",
        "company_id",
        "user_id",
        "brand_id",
        "plate_no",
        "engine_no",
        "chassis_no",
        "color",
        "model",
        "seats",
    ];

    public function casts() :array{
        return [
            'seats' => 'array',
        ];
    }

    public function brand(){
        return $this->belongsTo(VehicleBrand::class, 'brand_id', 'id');
    }

    public function driver(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
