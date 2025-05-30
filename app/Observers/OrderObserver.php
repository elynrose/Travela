<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Send order confirmation notification
        $order->user->notify(new OrderConfirmationNotification($order));
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if payment status was changed to completed
        if ($order->isDirty('payment_status') && $order->payment_status === 'completed') {
            // Increment purchase count
            $order->itinerary->increment('purchase_count');
            
            // Send payment success notification
            $order->user->notify(new PaymentSuccessNotification($order));
        }
        
        // Check if payment status was changed to failed
        if ($order->isDirty('payment_status') && $order->payment_status === 'failed') {
            // Send payment failure notification
            $order->user->notify(new PaymentFailedNotification(
                $order,
                $order->payment_details['message'] ?? 'Payment was declined by your bank.'
            ));
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // If needed, handle any cleanup when an order is deleted
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        // If needed, handle any logic when an order is restored
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        // If needed, handle any cleanup when an order is force deleted
    }
} 