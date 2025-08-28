@component('mail::message')
# {{ $mail_header }}

{!! $content !!}

@if(isset($actionText))
@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent
@endif

Thanks,<br>
{{ $mail_header }}
@endcomponent 