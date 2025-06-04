@component('mail::message')
# Welcome to {{ config('app.name') }}

Thank you for joining us! We're excited to have you on board.

@component('mail::button', ['url' => route('dashboard')])
Go to Dashboard
@endcomponent

If you have any questions, feel free to contact our support team.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 