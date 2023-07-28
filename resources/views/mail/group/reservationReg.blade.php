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
                    <th colspan="2" class="table-header text-uppercase">Бүртгэлийн хуудас</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <p><b>Хүндэт,</b> {{ $guest->name }} {{ $guest->surname }}</p>
                    </td>
                </tr>
                <tr class="text-center">
                    <td colspan="2" class="text-center">
                        <p>Биднийг сонгосон танд баярлалаа.</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Guest table -->
        <h4 class="text-uppercase mt-4">Хувийн мэдээлэл</h4>
        <table class="table-guest">
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
        </table>
        <!-- Table reservations -->
        <h4 class="text-uppercase mt-4 mb-0">Захиалгын мэдээлэл</h4>
        <table class="table-content mt-4" width="100%">
            <thead>
                <tr class="text-left">
                    <th>Захиалгын дугаар</th>
                    <th>Ирэх/Гарах өдөр</th>
                    <th>Өрөө</th>
                    <th>Том хүн/Хүүхэд</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group->reservations as $item)
                <tr>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->check_in }} <br> {{ $item->check_out }}</td>
                    <td>{{ $item->roomTypeClone->name }}</td>
                    <td class="text-center">{{ $item->number_of_guests }}/{{ $item->number_of_children }}</td>
                </tr>
                @endforeach
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
