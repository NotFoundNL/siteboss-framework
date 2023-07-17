@component('mail::message')

<h4>De gebruiker met ID: {{ $user->id }} en e-mail: {{$user->email}} is geblokkeerd.</h4>
<p>Dit is gegaan doormiddel van de blokkeer link in de verificatie email.
</p>

@endcomponent