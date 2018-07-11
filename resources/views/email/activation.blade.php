@component('mail::message')
# Hi {{ $name }},

Thanks for Registering on Our site,For activation clink here.

@component('mail::button', ['url' => env('APP_URL').'activate_account/'.$activation_token])
    Activate
@endcomponent

Thanks, <br>
{{ config('app.name') }}
@endcomponent