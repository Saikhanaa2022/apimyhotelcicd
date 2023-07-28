@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot
    <div>
        <h3>@lang('messages.greetings')</h3>
        <h4>XRoom захиалга баталгаажлаа</h4>
        <p>
            Захиалгын №: {{ $reservation->number }}
        </p>
        <h3>Төлбөрийн мэдээлэл</h3>
        <p>
            Төлөх: {{ $reservation->amount }}₮
        </p>
        <p>
            Нийт төлсөн: {{ $amount }}₮
            <br>
            <span style="color:red">Тухайн төлсөн дүн нь нийт захиалгын дүн бөгөөд уг захиалгын төлбөр орсонд тооцохыг анхаарна уу!</span>
        </p>
        <p>
            Төлбөрийн хэрэгсэл: {{ $payment_method }}
        </p>
        <br>
        <div class="text-center">
            @component('mail::button', ['url' => $actionUrl])
                Захиалга харах
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
                © {{ $year }} MyHotel RMS систем, "Айхотел" ХХК. Оюуны өмч хуулиар хамгаалагдсан.
            @else
                © {{ $year }} MyHotel RMS system. iHotel LLC. All rights reserved.
            @endif
        @endcomponent
    @endslot
@endcomponent
