<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompleted extends Notification implements ShouldQueue
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
            ->subject('Order Confirmation - ' . $this->order->order_number)
            ->greeting('Thank you for your purchase!')
            ->line('Your order has been successfully processed.')
            ->line('Order Details:')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Itinerary: ' . $this->order->itinerary->title)
            ->line('Amount: $' . number_format($this->order->amount, 2))
            ->line('Payment Status: ' . ucfirst($this->order->payment_status))
            ->action('View Order', route('orders.show', $this->order))
            ->line('You can access your purchased itinerary from your dashboard.')
            ->line('Thank you for using our platform!');
    }
} 