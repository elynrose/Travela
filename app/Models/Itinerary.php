<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'location',
        'country',
        'accommodation',
        'accommodation_address',
        'price',
        'duration_days',
        'highlights',
        'included_items',
        'excluded_items',
        'requirements',
        'is_published',
        'is_featured',
        'gallery',
        'transportation_type',
        'flight_duration',
        'airfare_min',
        'airfare_max',
        'booking_website',
        'road_distance',
        'road_duration',
        'road_type',
        'languages',
        'peak_travel_times',
        'travel_agency',
        'agency_fees',
        'travel_notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'highlights' => 'array',
        'included_items' => 'array',
        'excluded_items' => 'array',
        'requirements' => 'array',
        'gallery' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($itinerary) {
            if (empty($itinerary->slug)) {
                $itinerary->slug = Str::slug($itinerary->title) . '-' . time();
            }
        });

        static::updating(function ($itinerary) {
            if ($itinerary->isDirty('title') && !$itinerary->isDirty('slug')) {
                $itinerary->slug = Str::slug($itinerary->title) . '-' . time();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function days()
    {
        return $this->hasMany(Day::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getCoverImageUrl()
    {
        if (!$this->cover_image) {
            return null;
        }
        return Storage::url($this->cover_image);
    }

    public function getCoverThumbUrl()
    {
        if (!$this->cover_image) {
            return null;
        }
        return Storage::url(str_replace('covers/', 'covers/thumbnails/', $this->cover_image));
    }
}
