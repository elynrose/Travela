@component('mail::message')
# Payment Successful

Thank you for your payment. Your transaction has been completed successfully.

**Transaction Details:**
- Amount: ${{ number_format($amount / 100, 2) }}
- Date: {{ $date->format('F j, Y') }}
- Transaction ID: {{ $transactionId }}

@component('mail::button', ['url' => route('orders.show', $orderId)])
View Order Details
@endcomponent

If you have any questions about your payment, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 