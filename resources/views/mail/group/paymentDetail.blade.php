@component('mail::layout')
    {{-- Header --}}
    @slot('header')
    @endslot

    <div>
        <table class="table-head" width="100%">
            <tr>
                <th colspan="5" class="table-header text-uppercase">ТӨЛБӨРИЙН БАРИМТ</th>
            </tr>
            <tr class="sub-header">
                <td colspan="2" width="45%"><b>Захиалгын групп дугаар:</b> {{ $groupNumber }}</td>
                <td colspan="3" width="50%"><b>Зочны нэр:</b> {{ $guest->name }} {{ $guest->surname }}</td>
            </tr>
        </table>
        <table class="table-content mt-4" width="100%">
            <thead class="text-left">
                <tr>
                    <th>Огноо</th>
                    <th class="text-left">Үйлчилгээний нэр</th>
                    <th>Тоо</th>
                    <th>Нэгж үнэ</th>
                    <th>Нийт үнэ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['date'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-right">{{ number_format($item['price'], 0, '.', ',') }} <small>MNT</small></td>
                        <td class="text-right">{{ number_format($item['totalPrice'], 0, '.', ',') }} <small>MNT</small></td>
                    </tr>
                @endforeach
                @if (count($taxes) > 0)
                    <tr class="no-border">
                        <td colspan="2" class="tax-sigment"></td>
                        <td colspan="2" class="text-left border-bottom">Дүн:</td>
                        <td class="text-right border-bottom">{{ number_format($totalAmount, 0, '.', ',') }} <small>MNT</small></td>
                    </tr>
                @endif
                @foreach ($taxes as $item)
                    <tr class="no-border">
                        <td colspan="2" class="tax-sigment"></td>
                        <td colspan="2 border-bottom">{{ $item->name }}</td>
                        <td class="text-right border-bottom">{{ number_format($totalAmount * ($item->percentage / 100), 0, '.', ',') }} <small>MNT</small></td>
                    </tr>
                @endforeach
                <tr class="no-border">
                    <td colspan="2" class="tax-sigment"></td>
                    <td colspan="2" class="text-left border-bottom"><b>Нийт дүн:</b></td>
                    <td class="text-right border-bottom">{{ number_format($totalAmountTax, 0, '.', ',') }} <small>MNT</small></td>
                </tr>
            </tbody>
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
