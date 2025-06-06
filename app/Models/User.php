<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'stripe_account_id',
        'stripe_customer_id',
        'bio',
        'location',
        'is_admin',
        'is_blocked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->withResponsiveImages();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(300)
            ->height(300)
            ->sharpen(10);
    }

    /**
     * Get the user's avatar URL.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return asset('images/default-avatar.png');
    }

    public function getAvatarThumbUrlAttribute()
    {
        if ($this->avatar) {
            $path = str_replace('avatars/', 'avatars/thumbnails/', $this->avatar);
            return asset('storage/' . $path);
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Check if the user has purchased an itinerary
     *
     * @param \App\Models\Itinerary $itinerary
     * @return bool
     */
    public function hasPurchased($itinerary)
    {
        return $this->orders()
            ->where('itinerary_id', $itinerary->id)
            ->where('payment_status', 'completed')
            ->exists();
    }

    public function isCreator()
    {
        return $this->role === 'creator';
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
        $this->notify(new \App\Notifications\WelcomeNotification);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Get the total sales amount for the user's itineraries.
     *
     * @return float
     */
    public function getTotalSalesAttribute()
    {
        return $this->itineraries()
            ->whereHas('orders', function ($query) {
                $query->where('payment_status', 'completed');
            })
            ->withCount(['orders' => function ($query) {
                $query->where('payment_status', 'completed');
            }])
            ->get()
            ->sum('orders_count');
    }

    /**
     * Get the available balance (total sales minus platform fee and withdrawals).
     *
     * @return float
     */
    public function getAvailableBalanceAttribute()
    {
        $totalSalesAmount = $this->itineraries()
            ->whereHas('orders', function ($query) {
                $query->where('payment_status', 'completed');
            })
            ->withSum(['orders' => function ($query) {
                $query->where('payment_status', 'completed');
            }], 'amount')
            ->get()
            ->sum('orders_sum_amount');

        $platformFee = $totalSalesAmount * 0.30; // 30% platform fee
        $totalWithdrawn = $this->payouts()
            ->where('status', 'completed')
            ->sum('amount');

        return $totalSalesAmount - $platformFee - $totalWithdrawn;
    }

    /**
     * Get the total number of sales.
     *
     * @return int
     */
    public function getTotalSalesCountAttribute()
    {
        return $this->itineraries()
            ->whereHas('orders', function ($query) {
                $query->where('payment_status', 'completed');
            })
            ->withCount(['orders' => function ($query) {
                $query->where('payment_status', 'completed');
            }])
            ->get()
            ->sum('orders_count');
    }
}
