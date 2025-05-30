<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Order $order
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Confirmation - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for your order. We\'re excited to help you plan your next adventure!')
            ->line('Order Details:')
            ->line('• Order Number: ' . $this->order->order_number)
            ->line('• Itinerary: ' . $this->order->itinerary->title)
            ->line('• Amount: $' . number_format($this->order->amount, 2))
            ->line('• Date: ' . $this->order->created_at->format('F d, Y'))
            ->action('View Order Details', route('orders.show', $this->order))
            ->line('You can access your purchased itinerary anytime from your dashboard.')
            ->line('If you have any questions about your order, please don\'t hesitate to contact our support team.')
            ->line('Happy travels!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->amount,
        ];
    }
} 