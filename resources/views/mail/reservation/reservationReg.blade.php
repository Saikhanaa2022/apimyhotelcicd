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
                    <th colspan="2" class="table-header text-uppercase">Бүртгэлийн хуудас</th>
                </tr>
                <tr>
                    <td colspan="2" class="table-sub-header">
                        <b>Хүндэт,</b> {{ $reservation->guestClone->name }} {{ $reservation->guestClone->surname }}
                    </td>
                </tr>
                <tr class="text-center">
                    <td colspan="2" class="table-sub-header">
                        <div>Биднийг сонгосон танд баярлалаа.</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="table-sub-header"></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-padding text-uppercase">
                        <b>Захиалгын мэдээлэл</b>
                    </td>
                </tr>
                <tr>
                    <td width="25%"><b>Захиалгын дугаар:</b></td>
                    <td>{{ $reservation->number }}</td>
                </tr>
                <tr>
                    <td><b>Өрөө:</b></td>
                    <td>{{ $reservation->roomClone->name }} {{ $reservation->roomTypeClone->name }}</td>
                </tr>
                <tr>
                    <td><b>Ирэх өдөр:</b></td>
                    <td>{{ $reservation->check_in }}</td>
                </tr>
                <tr>
                    <td><b>Гарах өдөр:</b></td>
                    <td>{{ $reservation->check_out }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-padding text-uppercase">
                        <b>Хувийн мэдээлэл</b>
                    </td>
                </tr>
                <tr>
                    <td><b>Нэр:</b></td>
                    <td>{{ $guest->name }}</td>
                </tr>
                <tr>
                    <td><b>Овог:</b></td>
                    <td>{{ $guest->surname }}</td>
                </tr>
                <tr>
                    <td><b>Иргэншил:</b></td>
                    <td>{{ $guest->nationality }}</td>
                </tr>
                <tr>
                    <td><b>Регистрын (Пасспортын) дугаар}:</b></td>
                    <td>{{ $guest->passport_number }}</td>
                </tr>
                <tr>
                    <td><b>Цахим шуудан хаяг:</b></td>
                    <td>{{ $guest->email }}</td>
                </tr>
                <tr>
                    <td><b>Утасны дугаар:</b></td>
                    <td>{{ $guest->phone_number }}</td>
                </tr>
                @if ($reservation->notes != NULL && $reservation->notes != '')
                    <tr>
                        <td colspan="2" class="text-padding text-uppercase"><b>Нэмэлт тэмдэглэл</b></td>
                    </tr>
                    <tr>
                        <td colspan="2">{{ $reservation->notes }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        @if ($hotel->is_show_rules && count($hotel->hotelRules) > 0)
        <div class="mt-5">
            <h4 class="text-red">Та манай дүрэмтэй танилцана уу.</h4>
            @foreach ($hotel->hotelRules as $item)
                <div class="mt-4">
                    <div><b>0. {{ $item->title }}</b></div>
                    <div class="pl-4">{{ $item->description }}</div>
                </div>
            @endforeach
        </div>
        @endif
        <div class="mt-5">
            <table width="100%">
                <tr>
                    <td width="75%">
                        <b>Гарын үсэг:</b> / ............................................................. /
                    </td>
                    <td class="text-right">
                        <b>Огноо:</b> {{ $today }}
                    </td>
                </tr>
            </table>
        </div>
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
