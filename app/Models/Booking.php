<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_id',
        'quantity',
        'total_price',
        'status',
    ];
   
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }
    
    protected static function booted()
    {
        static::created(function ($booking) {
            if ($booking->ticket) {
                $booking->ticket->decrement('remaining', $booking->quantity);
            }
        });

        static::deleted(function ($booking) {
            if ($booking->ticket) {
                $booking->ticket->increment('remaining', $booking->quantity);
            }
        });
    }
}


