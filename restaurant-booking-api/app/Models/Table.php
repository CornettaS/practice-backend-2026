<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = ['restaurant_id', 'table_number', 'capacity', 'is_available'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}