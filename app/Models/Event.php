<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = "events";
    protected $fillable = [
        'title',
        'slug',
        'image',
        'start_date',
        'file',
        'end_date',
        'address',
        'is_publish',
    ];

    public function eventMedia()
    {
        return $this->hasMany(EventsMedia::class, 'event_id', 'id');
    }
}
