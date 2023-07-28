@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'blue';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Subcopy --}}
@isset($actionText)
@component('mail::subcopy')
@if (App::isLocale('mn'))
    @lang(
        "Хэрвээ \":actionText\" товч ажиллахгүй бол дараах URL буюу веб холбоосыг хуулан авч шинэ цонхонд нээнэ үү: ".
        '[:actionURL](:actionURL)',
        [
            'actionText' => $actionText,
            'actionURL' => $actionUrl,
        ]
    )
@else
    @lang(
        "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
        'into your web browser: [:actionURL](:actionURL)',
        [
            'actionText' => $actionText,
            'actionURL' => $actionUrl,
        ]
    )
@endif

@endcomponent
@endisset
@endcomponent
