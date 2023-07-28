@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot
    <div>
        <h3>@lang('messages.greetings')</h3>

        @if ($emailType != 'resReqPaid')
            <p>
                @if ($emailType == 'resReqConfirmed')
                    {{ $resReq->res_number }} дугаартай захиалгын хүсэлт баталгаажлаа.
                @else
                    {{ $resReq->sourceClone->name }} -с шинэ захиалгын хүсэлт орж ирлээ.
                @endif
            </p>
            <h4>Захиалгын мэдээлэл</h4>
            <table class="table-normal">
                <tr>
                    <td>Захиалгын дугаар</td>
                    <td>{{ $resReq->res_number }}</td>
                </tr>
                <tr>
                    <td>Захиалгын суваг</td>
                    <td>{{ $resReq->sourceClone->name }}</td>
                </tr>
                <tr>
                    <td>Зочны нэр</td>
                    <td>{{ json_decode($resReq->guest)->surname }} {{ json_decode($resReq->guest)->name }}</td>
                </tr>
                <tr>
                    <td>Байрлах хугацаа</td>
                    <td>{{ $resReq->stay_nights }} хоног</td>
                </tr>
                <tr>
                    <td>Захиалгын дүн</td>
                    <td>{{ number_format($resReq->amount) }} MNT</td>
                </tr>
                <tr>
                    <td>Хүлээн авах дүн</td>
                    <td>{{ number_format($resReq->amount) }} MNT * 2% = {{ number_format(($resReq->amount * (1 - 0.02)), 2) }} MNT</td>
                </tr>
            </table>
            <br>
            <h4>Захиалсан өрөөнүүд</h4>
            <table class="table-normal">
                <thead>
                    <tr>
                        <th class="text-left">Өрөөний төрөл</th>
                        <th>Тоо</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($resReq->reservedRoomTypes as $item)
                    <tr>
                        <td width="60%">{{ $item->name }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>{{ $resReq->res_number }} дугаартай захиалгын хүсэлтийн төлбөр төлөгдлөө.</p>

            <h4>Дэлгэрэнгүй мэдээлэл</h4>
            <table class="table-normal">
                <tr>
                    <td>Захиалгын дугаар</td>
                    <td>{{ $resReq->res_number }}</td>
                </tr>
                <tr>
                    <td>Захиалгын суваг</td>
                    <td>{{ $resReq->sourceClone->name }}</td>
                </tr>
                <tr>
                    <td>Захиалгын дүн</td>
                    <td>{{ number_format($resReq->amount) }} MNT</td>
                </tr>
                <tr>
                    <td>Тооцсон шимтгэл</td>
                    <td>{{ $resReq->commission }} %</td>
                </tr>
                <tr>
                    <td>Төлөгдсөн дүн</td>
                    <td>{{ number_format($resReq->amount_paid) }} MNT</td>
                </tr>
            </table>
        @endif
        <br>
        <div class="text-center">
            @component('mail::button', ['url' => $actionUrl])
                Захиалгын хүсэлт харах
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
