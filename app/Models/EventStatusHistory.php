<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventStatusHistory extends Model
{
    protected $fillable = [
        'event_id',
        'old_status',
        'new_status',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
