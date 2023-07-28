@component('mail::layout')
    {{-- Header --}}
    @slot('header')
    @endslot

    <div class="print-table">
        <table>
            <tbody>
                <tr>
                    <th colspan="4">НЭХЭМЖЛЭХ №  INV - {{ $invoice->reservation_number }}</th>
                    <th>
                        @if($invoice->hotel_image)
                            <img src="{{ url('/image?path='.$invoice->hotel_image .'&w=50&fit=crop') }}" width="50" />
                        @else
                            {{ $invoice->hotel_name }}
                        @endif
                    </th>
                </tr>
                <tr>
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <td colspan="2" width="45%"><b>Нэхэмжлэгч:</b></td>
                    <td colspan="3" width="50%"><b>Төлөгч:</b></td>
                </tr>
                <tr>
                    <td colspan="2"><b>Байгууллагын нэр:</b> {{ $invoice->hotel_company_name }}</td>
                    <td colspan="3"><b>Байгууллагын нэр:</b> {{ $invoice->customer_name }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="3"><b>Зочны нэр:</b> {{ $invoice->guest_name }} {{ $invoice->guest_surname }}</td>
                </tr>
                <tr>
                    <td colspan="2"><b>Татвар төлөгчийн дугаар:</b> {{ $invoice->hotel_register_no }}</td>
                    <td colspan="3"><b>Татвар төлөгчийн дугаар:</b> {{ $invoice->register_no }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-right: 15px;"><b>Хаяг:</b> {{ $invoice->hotel_address }}</td>
                    <td colspan="3" valign="top" style="padding-right: 15px;"><b>Хаяг:</b> {{ $invoice->address }}</td>
                </tr>
                <tr>
                    <td colspan="2"><b>Утас:</b> {{ $invoice->hotel_phone }}</td>
                    <td colspan="3"><b>Утас:</b> {{ $invoice->phone_number }}</td>
                </tr>
                <tr>
                    <td colspan="2"><b>Цахим шуудан:</b> {{ $invoice->hotel_email }}</td>
                    <td colspan="3"><b>Гэрээний №:</b> {{ $invoice->contract_no }}</td>
                </tr>
                @if($invoice->tour_code !== null && $invoice->tour_code !== '')
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="3"><b>Tour Code:</b> {{ $invoice->tour_code }}</td>
                    </tr>
                @endif
                @if($invoice->voucher_code !== null && $invoice->voucher_code !== '')
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="3"><b>Voucher Code:</b> {{ $invoice->voucher_code }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="2"></td>
                    <td colspan="3"><b>Нэхэмжилсэн огноо:</b> {{ $invoice->invoice_date }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="3"><b>Төлбөр хийх хугацаа:</b> {{ $invoice->payment_period }}</td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                </tr>
                <tr class="header">
                    <th>Огноо</th>
                    <th width="30%">Үйлчилгээний нэр</th>
                    <th width="10%">Тоо</th>
                    <th width="25%">Нэг бүрийн үнэ</th>
                    <th width="25%">Нийт үнэ</th>
                </tr>
                @foreach($items as $item)
                    @if($item->item_type == 'room' || $item->item_type == 'item')
                        <tr class="body">
                            <td>{{ $item->date }}</td>
                            <td width="30%">{{ $item->name }}</td>
                            <td width="10%">{{ $item->quantity }}</td>
                            <td width="25%">{{ number_format($item->price, 0, ',', ',') }} <small>MNT</small></td>
                            <td width="25%">{{ number_format($item->price * $item->quantity, 0, ',', ',') }} <small>MNT</small></td>
                        </tr>
                    @endif
                @endforeach
                @if(count($taxes))
                    {{-- Total amount --}}
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2" class="text-left">Дүн:</td>
                        <td class="text-right">{{ number_format($invoice->total_amount, 0, ',', ',') }} <small>MNT</small></td>
                    </tr>
                @endif
                @foreach($taxes as $tax)
                    <tr v-for="item in selectedTaxes" :key="item.id">
                        <td colspan="2"></td>
                        <td  colspan="2" class="text-left">{{ $tax->name }}:</td>
                        <td class="text-right">{{ number_format($tax->price, 0, ',', ',') }} <small>MNT</small></td>
                    </tr>
                @endforeach
                {{-- Full total Amount --}}
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2" class="text-left"><b>Нийт дүн:</b></td>
                    <td class="text-right">{{ number_format($invoice->full_total_amount, 0, ',', ',') }} <small>MNT</small></td>
                </tr>
                <tr class="full-amount-first" ><td colspan="5"></td></tr>
                <tr>
                    <td colspan="5"><b>Төлбөр төлөх дансны мэдээлэл:</b></td>
                </tr>
                @foreach(json_decode($invoice->hotel_banks) as $hb)
                <tr class="bank-accounts">
                    <td colspan="5">{{ $hb->bank }} : {{ $hb->accountName }} : {{ $hb->number }} / {{ $hb->currency }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td colspan="1"></td>
                    <td colspan="4">Захирал:.............................../................................../</td>
                </tr>
                <tr>
                    <td colspan="1"></td>
                    <td colspan="4">Нягтлан бодогч:......................../…....................../</td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <th colspan="5">МАНАЙД ТУХЛАН СААТСАНД БАЯРЛАЛАА</th>
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
