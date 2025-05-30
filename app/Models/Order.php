<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'itinerary_id',
        'order_number',
        'amount',
        'platform_fee',
        'seller_amount',
        'currency',
        'payment_status',
        'payment_method',
        'stripe_payment_id',
        'stripe_customer_id',
        'payment_details',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }
}
