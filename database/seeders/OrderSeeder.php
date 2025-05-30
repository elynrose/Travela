<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Itinerary;
use App\Models\User;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the Safari Adventure in Tanzania itinerary
        $itinerary = Itinerary::where('title', 'Safari Adventure in Tanzania')->first();
        
        if (!$itinerary) {
            $this->command->error('Safari Adventure in Tanzania itinerary not found!');
            return;
        }

        // Find your user account
        $user = User::where('email', 'user@example.com')->first();
        
        if (!$user) {
            $this->command->error('User not found!');
            return;
        }

        // Create a completed order
        Order::create([
            'user_id' => $user->id,
            'itinerary_id' => $itinerary->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'amount' => $itinerary->price,
            'platform_fee' => $itinerary->price * 0.30,
            'seller_amount' => $itinerary->price * 0.70,
            'currency' => 'USD',
            'payment_status' => 'completed',
            'payment_method' => 'card',
            'stripe_payment_id' => 'seeded_payment_' . time(),
            'paid_at' => now(),
        ]);

        $this->command->info('Successfully created a completed order for Safari Adventure in Tanzania!');
    }
}
