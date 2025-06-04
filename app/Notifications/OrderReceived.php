<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Order Received - ' . $this->order->order_number)
            ->greeting('Congratulations! You have received a new order.')
            ->line('A customer has purchased your itinerary.')
            ->line('Order Details:')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Itinerary: ' . $this->order->itinerary->title)
            ->line('Amount: $' . number_format($this->order->amount, 2))
            ->line('Your Earnings: $' . number_format($this->order->seller_amount, 2))
            ->action('View Order', route('orders.show', $this->order))
            ->line('The payment has been processed and your earnings will be available for withdrawal according to our payout schedule.')
            ->line('Thank you for being a part of our platform!');
    }
} 