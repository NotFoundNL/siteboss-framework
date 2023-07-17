{{-- blade-formatter-disable --}}
<x-mail::message>
# {{ __('Welkom bij Veiligheids Coalitie') }}

{{ __('Klik op onderstaande link om je e-mailadres te bevestigen.') }}

<x-mail::button :url="$url">
{{ __('Bevestig e-mailadres') }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}

[{{__('Klik hier als jij dit niet was.')}}]({{$blockUrl}}) 
</x-mail::message>
