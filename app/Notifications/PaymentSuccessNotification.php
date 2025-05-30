<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification implements ShouldQueue
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
            ->subject('Payment Successful - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been processed successfully.')
            ->line('Payment Details:')
            ->line('• Order Number: ' . $this->order->order_number)
            ->line('• Amount: $' . number_format($this->order->amount, 2))
            ->line('• Payment Method: ' . ucfirst($this->order->payment_method))
            ->line('• Transaction ID: ' . $this->order->stripe_payment_id)
            ->line('• Date: ' . $this->order->paid_at->format('F d, Y'))
            ->action('View Order Details', route('orders.show', $this->order))
            ->line('You can now access your purchased itinerary from your dashboard.')
            ->line('A receipt has been sent to your email address.')
            ->line('If you have any questions about your payment, please contact our support team.');
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
            'payment_id' => $this->order->stripe_payment_id,
        ];
    }
} 