@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot
    <div>
        <h3>@lang('messages.greetings')</h3>

        <div style="padding: 15px 0; font-size: 16px">
            <b>Захиалгын суваг:</b> {{ $firstRes->sourceClone->name }}
            <br>
            <b>Захиалгын групп дугаар:</b> 
            <a href="{{ $actionUrl }}" target="_blank" title="Захиалга харах">{{ $group->number }}</a>
            <br>
            <b>Ирэх өдөр:</b> {{ $firstRes->check_in }}
            <br>
            <b>Гарах өдөр:</b> {{ $firstRes->check_out }}
            <br>
            <b>Хугацаа:</b> {{ $firstRes->stay_nights }} хоног
        </div>
        <hr>
        @foreach ($group->reservations as $item)
            <div>
                <b>Захиалгын дугаар:</b> {{ $item->number }}<br>
                <b>Зочны тоо:</b> {{ $item->number_of_guests }}<br>
                <b>Өрөөний төрөл:</b> {{ $item->roomTypeClone->name }}<br>
                <b>Үнэ:</b> {{ number_format($item->amount) }} <span style="font-size: 10px">MNT</span><br>
            </div>
            <hr>
        @endforeach
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
                © 2019 MyHotel RMS систем, "Айхотел" ХХК. Оюуны өмч хуулиар хамгаалагдсан.
            @else
                © 2019 MyHotel RMS system. iHotel LLC. All rights reserved.
            @endif
        @endcomponent
    @endslot
@endcomponent
