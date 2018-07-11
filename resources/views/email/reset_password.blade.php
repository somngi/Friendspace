@component('mail::message')
# Hi {{ $name }},

Your Password reset link is here. please click "Reset Password Button".

@component('mail::button', ['url' => env('APP_URL').'reset_password/'.$activation_token])
Reset Password
@endcomponent

Thanks, <br>
{{ config('app.name') }}
@endcomponent
