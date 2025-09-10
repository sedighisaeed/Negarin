@component('mail::message')
# Message from {{ config('negarin.domain.app') }}:


@component('mail::panel')
{{$msg}}
@endcomponent


<br>

Regards,<br>
{{ config('negarin.domain.app') }}

@component('mail::subcopy')
Please do not reply to this email, this address is not monitored.
@endcomponent

@endcomponent








