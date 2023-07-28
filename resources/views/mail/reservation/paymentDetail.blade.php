@component('mail::layout')
    {{-- Header --}}
    @slot('header')
    @endslot

    <div class="payment-table">
        <table>
            <tr>
                <th colspan="5" class="table-header text-uppercase">ТӨЛБӨРИЙН БАРИМТ</th>
            </tr>
            <tr>
                <th colspan="5"></th>
            </tr>
            <tr class="sub-header">
                <td colspan="2" width="45%"><b>Захиалгын дугаар:</b> {{ $reservation->number }}</td>
                <td colspan="3" width="50%"><b>Зочны нэр:</b> {{ $reservation->guestClone->name }} {{ $reservation->guestClone->surname }}</td>
            </tr>
            <tr>
                <th>Огноо</th>
                <th class="text-left">Үйлчилгээний нэр</th>
                <th>Тоо</th>
                <th>Нэгж үнэ</th>
                <th>Нийт үнэ</th>
            </tr>
            <tr>
                <td class="text-center">
                    @if ($reservation->is_time)
                        {{ date('y/m/d H:i', strtotime($reservation->check_in)) . ' - ' . date('y/m/d H:i', strtotime($reservation->check_out)) }}
                    @else
                        {{ date('y/m/d', strtotime($reservation->check_in)) . ' - ' . date('y/m/d', strtotime($reservation->check_out)) }}
                    @endif
                </td>
                <td class="text-left">{{ $reservation->roomTypeClone->name }}</td>
                <td class="text-center">{{ $reservation->is_time ? '1' : $reservation->stayNights }}</td>
                <td class="text-right">
                    @if ($reservation->is_time)
                        {{ number_format($reservation->amount, 0, '.', ',') }}
                    @else
                        {{ number_format($roomTotalPrice / $reservation->stayNights, 0, '.', ',') }}
                    @endif
                    <small>MNT</small>
                </td>
                <td class="text-right">{{ number_format($roomTotalPrice, 0, '.', ',') }} <small>MNT</small></td>
            </tr>
            @foreach ($reservation->items as $key => $item)
                <tr class="body">
                    <td class="text-center">{{ date('y/m/d H:m', strtotime($item->created_at)) }}</td>
                    <td class="text-left">{{ $item->serviceCategoryClone->name }} - {{ $item->serviceClone->name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 0, '.', ',') }} <small>MNT</small></td>
                    <td class="text-right">{{ number_format($item->price * $item->quantity, 0, '.', ',') }} <small>MNT</small></td>
                </tr>
            @endforeach
            @foreach ($reservation->extraBeds as $key => $item)
                <tr class="body">
                    <td class="text-center">{{ date('y/m/d H:m', strtotime($item->created_at)) }}</td>
                    <td class="text-left">Нэмэлт ор</td>
                    <td class="text-center">{{ $item->nights }}</td>
                    <td class="text-right">{{ number_format($item->amount, 0, '.', ',') }} <small>MNT</small></td>
                    <td class="text-right">{{ number_format($item->amount * $item->nights, 0, '.', ',') }} <small>MNT</small></td>
                </tr>
            @endforeach
            @if (count($reservation->taxClones) > 0)
                <tr>
                    <td colspan="2" class="tax-sigment"></td>
                    <td colspan="2" class="text-left">Дүн:</td>
                    <td class="text-right">{{ number_format($totalAmount, 0, '.', ',') }} <small>MNT</small></td>
                </tr>
            @endif
            @foreach ($reservation->taxClones as $item)
                <tr>
                    <td colspan="2" class="tax-sigment"></td>
                    <td colspan="2">{{ $item->name }}</td>
                    <td class="text-right">{{ number_format($totalAmount * ($item->percentage / 100), 0, '.', ',') }} <small>MNT</small></td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="tax-sigment"></td>
                <td colspan="2" class="text-left"><b>Нийт дүн:</b></td>
                <td class="text-right">{{ number_format($totalAmountTax, 0, '.', ',') }} <small>MNT</small></td>
            </tr>
        </table>
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
