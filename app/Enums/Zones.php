<?php

namespace App\Enums;

enum Zones :string
{
    case NORTHCENTRAL = 'north-central';
    case NORTHEAST = 'north-east';
    case NORTHWEST = 'north-west';
    case SOUTHEAST = 'south-east';
    case SOUTHWEST = 'south-west';
    case SOUTHSOUTH = 'south-south';

    public function states(){
        return match($this){
            self::NORTHCENTRAL => ['Benue', 'FCT', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Plateau'],
            self::NORTHEAST => ['Adamawa', 'Bauchi', 'Borno', 'Gombe', 'Taraba', 'Yobe'],
            self::NORTHWEST => ['Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Sokoto', 'Zamfara'],
            self::SOUTHEAST => ['Abia', 'Anambra', 'Ebonyi', 'Enugu', 'Imo'],
            self::SOUTHWEST => ['Ekiti', 'Lagos', 'Ogun', 'Ondo', 'Osun', 'Oyo'],
            self::SOUTHSOUTH => ['Akwa Ibom', 'Bayelsa', 'Cross River', 'Delta', 'Edo', 'Rivers'],
        };
    }
}
