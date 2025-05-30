<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItineraryDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'itinerary_id',
        'day_number',
        'title',
        'description',
        'activities',
        'meals',
        'accommodation',
        'transportation',
        'tips',
    ];

    protected $casts = [
        'activities' => 'array',
        'meals' => 'array',
        'accommodation' => 'array',
        'transportation' => 'array',
        'tips' => 'array',
    ];

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function items()
    {
        return $this->hasMany(ItineraryItem::class);
    }
}
