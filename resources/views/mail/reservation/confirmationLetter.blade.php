@component('mail::layout')
    {{-- Header --}}
    @slot('header')
    @endslot
    <div>
        <table class="table-normal mt-5" width="100%">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header text-center">
                        @if($hotel->image)
                            <img src="{{ url('/image?path='.$hotel->image .'&w=50&fit=crop') }}" width="50" />
                        @else
                            {{ $hotel->name }}
                        @endif
                    </th>
                </tr>
                <tr>
                    <th colspan="2" class="table-header text-uppercase">Захиалгын хуудас</th>
                </tr>
                <tr>
                    <td colspan="2" class="table-sub-header">
                        <b>Хүндэт, </b>{{ $reservation->guestClone->name }} {{ $reservation->guestClone->surname }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="table-sub-header text-center">
                    @if ($hotelSetting->email_header)
                        {!! $hotelSetting->email_header !!}                     
                    @else
                        Биднийг сонгосон танд баярлалаа. Таны захиалгын бүртгэлийг илгээж байна.
                    @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="table-sub-header text-right py-0"><b>Баталгаажсан огноо:</b> {{ date('Y/m/d', strtotime($reservation->check_in)) }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="table-sub-header"></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-padding text-uppercase"><b>Захиалгын мэдээлэл</b></td>
                </tr>
                <tr>
                    <td width="25%"><b>Захиалгын дугаар:</b></td>
                    <td>{{ $reservation->number }}</td>
                </tr>
                <tr>
                    <td width="25%"><b>Зочны нэр:</b></td>
                    <td>{{ $reservation->guestClone->name }} {{ $reservation->guestClone->surname }}</td>
                </tr>
                <tr>
                    <td width="25%"><b>Ирэх өдөр:</b></td>
                    <td>{{ $reservation->check_in }}</td>
                </tr>
                <tr>
                    <td width="25%"><b>Гарах өдөр:</b></td>
                    <td>{{ $reservation->check_out }}</td>
                </tr>
                <tr>
                    <td width="25%"><b>Хугацаа:</b></td>
                    <td>{{ $reservation->stay_nights }} {{ $reservation->is_time ? 'цаг' : 'хоног' }}</td>
                </tr>
                <tr>
                    <td width="25%"><b>Том хүн/Хүүхэд тоо:</b></td>
                    <td>{{ $reservation->number_of_guests }}/{{ $reservation->number_of_children }}</td>
                </tr>
                @if(!$reservation->is_time)
                <tr>
                    <td width="25%"><b>Стандарт ирэх цаг:</b></td>
                    <td>{{ $hotel->check_in_time }}</td>
                </tr>
                <tr>
                    <td width="25%"><b>Стандарт гарах цаг:</b></td>
                    <td>{{ $hotel->check_out_time }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="2" class="text-padding text-uppercase"><b>Үнийн мэдээлэл</b></td>
                </tr>
                <tr>
                    <td><b>Өрөө:</b></td>
                    <td>{{ $reservation->roomTypeClone->name }}</td>
                </tr>
                @if (!$reservation->is_time)
                <tr>
                    <td><b>1 өдрийн үнэ:</b></td>
                    @if ($reservation->stay_nights !== 0)
                    <td>{{ number_format($roomTotalPrice / $reservation->stay_nights, 0, '', ',') }} <small>MNT</small></td>
                    @else
                    <td>0</td>
                    @endif
                </tr>
                <tr>
                    <td><b>Төлбөр төлөх:</b></td>
                    <td>Төлбөрийг үндсэн зээлийн картаар эсвэл бэлэн мөнгөөр хийх боломжтой. Валютын ханш ирэх өдрийн Монгол банкны ханшийн дагуу байх болно.</td>
                </tr>
                @else
                <tr>
                    <td><b>Үнэ:</b></td>
                    <td>{{ number_format($reservation->amount, 0, '', ',') }} <small>MNT</small></td>
                </tr>
                @endif
                <tr>
                    <td><b>Хүсэлт:</b></td>
                    <td>{{ $reservation->notes }}</td>
                </tr>
            </tbody>
        </table>
        @if ($hotel->is_show_rules && count($hotel->hotelRules) > 0)
        <div class="mt-5">
            <h4 class="text-red">Та манай дүрэмтэй танилцана уу.</h4>
            @foreach ($hotel->hotelRules as $item)
                <div class="mt-4">
                    <div><b>{{ ++$loop->index }}. {{ $item->title }}</b></div>
                    <div class="pl-4">{{ $item->description }}</div>
                </div>
            @endforeach
        </div>
        @endif
        @if ($hotelSetting->email_body)
            <br>
            <div class="pre-line">{!! $hotelSetting->email_body !!}</div>
            <br>
        @endif
        @if ($hotel->is_show_payment && count($hotel->hotelBanks) > 0)
        <div class="mt-5">
            <h4>Төлбөр төлөх дансны мэдээлэл:</h4>
            <table width="100%" class="table-content mt-4">
                <thead>
                    <tr>
                        <th class="text-left">Банк</th>
                        <th class="text-left">Дансны дугаар</th>
                        <th class="text-left">Дансны нэр</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hotel->hotelBanks as $item)
                    <tr>
                        <td>{{ $item->bank->name }} ({{ $item->currency }})</td>
                        <td>{{ $item->number }}</td>
                        <td>{{ $item->account_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if ($hotelSetting->email_footer)
            <br>
            <div class="pre-line">{!! $hotelSetting->email_footer !!}</div>
        @endif
        @if ($hotelSetting->email_contact)
            <br>
            <br>
            <div class="pre-line">{!! $hotelSetting->email_contact !!}</div>
        @endif
    </div>

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
    @endslot
@endcomponent
