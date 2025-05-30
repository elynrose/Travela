<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItineraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'itinerary_day_id',
        'type',
        'title',
        'description',
        'location',
        'start_time',
        'end_time',
        'cost',
        'currency',
        'details',
        'image',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cost' => 'decimal:2',
        'details' => 'array',
    ];

    public function day()
    {
        return $this->belongsTo(ItineraryDay::class, 'itinerary_day_id');
    }
}
