@component('mail::message')
# Bonjour,

Veuillez trouver en pièce jointe votre facture.

@component('mail::button', ['url' => url('/')])
Accéder au site
@endcomponent

Merci,
{{ config('app.name') }}
@endcomponent
