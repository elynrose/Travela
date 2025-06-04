<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessful extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $date;
    protected $transactionId;
    protected $orderId;

    public function __construct($amount, $date, $transactionId, $orderId)
    {
        $this->amount = $amount;
        $this->date = $date;
        $this->transactionId = $transactionId;
        $this->orderId = $orderId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Successful')
            ->markdown('emails.payment-success', [
                'amount' => $this->amount,
                'date' => $this->date,
                'transactionId' => $this->transactionId,
                'orderId' => $this->orderId,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'amount' => $this->amount,
            'date' => $this->date,
            'transaction_id' => $this->transactionId,
            'order_id' => $this->orderId,
        ];
    }
} 