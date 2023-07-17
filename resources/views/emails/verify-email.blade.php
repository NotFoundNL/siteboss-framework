{{-- blade-formatter-disable --}}
<x-mail::message>
# {{ __('Welkom bij Veiligheids Coalitie') }}

{{ __('siteboss::auth.verify_email_link') }}

<x-mail::button :url="$url">
{{ __('siteboss::auth.verify_email_button') }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}

[{{__('siteboss::auth.verify_wrong_email')}}]({{$blockUrl}}) 
</x-mail::message>
