<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    
    public $timestamps = true;  
    
    protected $fillable = [
        'user_id', 'table_id', 'booking_date', 
        'booking_time', 'guests_count', 'status', 'duration' 
    ];

    protected $casts = [
        'booking_date' => 'date:Y-m-d',
        'booking_time' => 'datetime:H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    
    const DEFAULT_DURATION = 2; 

    public function getBookingTimeFormattedAttribute()
    {
        return $this->booking_time ? substr($this->booking_time, 0, 5) : null;
    }
    
    public function getEndTimeAttribute()
    {
        if (!$this->booking_time) return null;
        $duration = $this->duration ?? self::DEFAULT_DURATION;
        return date('H:i:s', strtotime($this->booking_time) + ($duration * 3600));
    }
    
    public function getEndTimeFormattedAttribute()
    {
        return $this->end_time ? substr($this->end_time, 0, 5) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}