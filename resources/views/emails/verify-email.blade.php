{{-- blade-formatter-disable --}}
<x-mail::message>
# {{ __('siteboss::auth.verify_email_header') }} {{ config('app.name') }}

{{ __('siteboss::auth.verify_email_link') }}

<x-mail::button :url="$url">
{{ __('siteboss::auth.verify_email_button') }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}

[<h4>{{__('siteboss::auth.verify_wrong_email')}}</h4>]({{$blockUrl}})
</x-mail::message>
