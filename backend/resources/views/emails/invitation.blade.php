@component('mail::message')
# You have been invited to join an organization

Click the button below to accept the invitation and create your account.

@component('mail::button', ['url' => $url])
Accept Invitation
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
