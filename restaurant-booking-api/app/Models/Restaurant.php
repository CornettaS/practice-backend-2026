<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'name', 'city_id', 'address', 'phone', 
        'description', 'capacity', 'opening_time', 'closing_time'
    ];

    protected $casts = [
        'opening_time' => 'string',
        'closing_time' => 'string',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Table::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'user_favorites');
    }
}