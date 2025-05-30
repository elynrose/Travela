<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Order $order,
        protected string $reason = 'Payment was declined by your bank.'
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
            ->subject('Payment Failed - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We were unable to process your payment.')
            ->line('Order Details:')
            ->line('• Order Number: ' . $this->order->order_number)
            ->line('• Amount: $' . number_format($this->order->amount, 2))
            ->line('• Date: ' . $this->order->created_at->format('F d, Y'))
            ->line('Reason: ' . $this->reason)
            ->action('Try Payment Again', route('orders.show', $this->order))
            ->line('Please try the following:')
            ->line('• Check if your card details are correct')
            ->line('• Ensure you have sufficient funds')
            ->line('• Try using a different payment method')
            ->line('If you continue to experience issues, please contact our support team for assistance.');
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
            'reason' => $this->reason,
        ];
    }
} 