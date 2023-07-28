@component('mail::layout')
    {{-- Header --}}
    @slot('header')
    @endslot

    <div>
        <table class="table-head" width="100%">
            <tbody>
                <tr>
                    <th colspan="2" class="table-header text-center">
                        @if ($hotel->image)
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
                    <td colspan="2">
                        <p><b>Хүндэт,</b> {{ $guest->name }} {{ $guest->surname }}</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p>
                            @if ($hotelSetting->email_header)
                                {!! $hotelSetting->email_header !!}                     
                            @else
                                Биднийг сонгосон танд баярлалаа. Таны захиалгын бүртгэлийг илгээж байна.
                            @endif
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="table-content mt-4" width="100%">
            <thead>
                <tr class="text-left">
                    <th>Ирэх/Гарах өдөр</th>
                    <th>Өрөө</th>
                    <th>Том хүн/Хүүхэд</th>
                    <th>Үнэ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group->reservations as $item)
                <tr>
                    <td>
                        @if ($item->is_time)
                            {{ date('y/m/d H:i', strtotime($item->check_in)) }}<br>{{ date('y/m/d H:i', strtotime($item->check_out)) }}
                        @else
                            {{ date('y/m/d', strtotime($item->check_in)) }}<br>{{ date('y/m/d', strtotime($item->check_out)) }}
                        @endif
                    </td>
                    <td>{{ $item->roomTypeClone->name }}</td>
                    <td class="text-center">{{ $item->number_of_guests }}/{{ $item->number_of_children }}</td>
                    <td class="text-right">{{ number_format($item->amount, 0, '', ',') }} <small>MNT<small></td>
                </tr>
                @endforeach
                <tr class="no-border">
                    <td colspan="2"></td>
                    <td class="border-bottom"><b>Нийт дүн:</b></td>
                    <td class="text-right border-bottom">{{ number_format($totalPrice, 0, '', ',') }} <small>MNT<small></td>
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
