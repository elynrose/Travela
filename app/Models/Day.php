<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'itinerary_id',
        'day_number',
        'accommodation',
        'accommodation_address',
        'meals',
        'activities',
        'notes',
        'receipts'
    ];

    protected $casts = [
        'meals' => 'array',
        'activities' => 'array',
        'receipts' => 'array',
    ];

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    // Helper methods for media
    public function getMealPhotos($mealType)
    {
        if (!isset($this->meals[$mealType]['photos'])) {
            return collect([]);
        }

        return collect($this->meals[$mealType]['photos'])->map(function ($photo) {
            return [
                'url' => Storage::url($photo['path']),
                'thumb_url' => Storage::url($photo['thumb_path']),
                'path' => $photo['path']
            ];
        });
    }

    public function getActivityPhotos($activityIndex)
    {
        if (!isset($this->activities[$activityIndex]['photos'])) {
            return collect([]);
        }

        return collect($this->activities[$activityIndex]['photos'])->map(function ($photo) {
            return [
                'url' => Storage::url($photo['path']),
                'thumb_url' => Storage::url($photo['thumb_path']),
                'path' => $photo['path']
            ];
        });
    }

    public function getReceipts()
    {
        if (!isset($this->receipts)) {
            return collect([]);
        }

        return collect($this->receipts)->map(function ($receipt) {
            return [
                'url' => Storage::url($receipt['path']),
                'name' => $receipt['name'],
                'mime_type' => $receipt['mime_type'],
                'path' => $receipt['path']
            ];
        });
    }
}
