<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

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
            ->subject('Welcome to ' . config('app.name'))
            ->greeting('Welcome ' . $notifiable->name . '!')
            ->line('Thank you for joining ' . config('app.name') . '. We\'re excited to have you on board!')
            ->line('With your account, you can:')
            ->line('• Browse and purchase travel itineraries')
            ->line('• Create and sell your own itineraries')
            ->line('• Connect with other travelers')
            ->line('• Manage your bookings and payments')
            ->action('Get Started', url('/dashboard'))
            ->line('If you have any questions, feel free to contact our support team.')
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
            //
        ];
    }
} 