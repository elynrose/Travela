<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderCreated extends Notification implements ShouldQueue
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
            ->subject('New Order Pending - ' . $this->order->order_number)
            ->greeting('New Order Alert!')
            ->line('Someone has initiated a purchase of your itinerary.')
            ->line('Order Details:')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Itinerary: ' . $this->order->itinerary->title)
            ->line('Amount: $' . number_format($this->order->amount, 2))
            ->line('Potential Earnings: $' . number_format($this->order->seller_amount, 2))
            ->action('View Order', route('orders.show', $this->order))
            ->line('This order is pending payment. You will receive another notification once the payment is completed.')
            ->line('Thank you for being a part of our platform!');
    }
} 