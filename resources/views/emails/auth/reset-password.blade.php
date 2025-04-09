<x-mail::message>
{{-- CIT Logo --}}

{{-- Title --}}
# Hello, **{{$greetingName}}**!

We received a request to reset your password. Click the button below to proceed.

{{-- Reset Button --}}
<x-mail::button :url="$resetUrl" color="primary">
Reset Password
</x-mail::button>

If you did not request this, please ignore this email.
<br>
<br>
Thanks,  
**{{ config('app.name') }}**

<div style="border-top: 2px solid #CCCCCC; margin: 20px 0;"></div> 
{{-- Plain URL at the bottom --}}
@lang("If you're having trouble clicking the \":actionText\" button, copy and paste this URL into your browser:", ['actionText' => 'Reset Password'])
<br>
<span class="break-all"><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></span>
</x-mail::message>
