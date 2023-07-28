@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            @if (App::isLocale('mn'))
                © 2019 MyHotel RMS систем, "Айхотел" ХХК. Оюуны өмч хуулиар хамгаалагдсан.
            @else
                © 2019 MyHotel RMS system. iHotel LLC. All rights reserved.
            @endif
        @endcomponent
    @endslot
@endcomponent
