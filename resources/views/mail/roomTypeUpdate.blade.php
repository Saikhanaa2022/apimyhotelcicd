@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot
    <div>
        <h3>@lang('messages.greetings')</h3>
        <h4>Өрөөний мэдээлэл шинэчлэгдлээ</h4>
        <p>
            Өөрчлөгдсөн дүн: {{ $roomType->default_price }}
        </p>
        <p>
            Өөрчлөлт хийсэн: {{ $user }}
        </p>
        
        <br>
        <div class="text-center">
            @component('mail::button', ['url' => $actionUrl])
                Өөрчлөлт харах
            @endcomponent
        </div>
    </div>

    {{-- Subcopy --}}
    @slot('subcopy')
    @endslot

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
