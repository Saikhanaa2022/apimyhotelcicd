<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{Item, Reservation};

class ReportController extends Controller
{
    /**
     * Return payment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesReport(Request $request)
    {
        // Validation
        $request->validate([
            'type' => 'required|string|in:today,week,month,year',
            'date' => 'required|array'
        ]);

        $type = $request->query('type');
        $hotel = request()->hotel;
        // $date = Carbon::parse($hotel->working_date);
        $date = Carbon::today();
        $startDate = $endDate = $date;
        $isDate = false;

        if ($type === 'week') {
            $startDate = $date->startOfWeek()->format('Y-m-d');
            $endDate = $date->endOfWeek()->format('Y-m-d');
        }
        if (!($request->query('date', null)[0] === null && $request->query('date', null)[1] === null)) {
            $startDate = $request->query('date', null)[0];
            $endDate = $request->query('date', null)[1];
            $isDate = true;
            $type = 'week';
        }

        $salesReport = [
            'sales' => $this->saleReport($date, $startDate, $endDate, $type, $hotel, $isDate),
            'room_sales' => $this->roomTypesReport($date, $startDate, $endDate, $type, $hotel, $isDate),
            'income_type' => $this->incomeTypesReport($date, $startDate, $endDate, $type, $hotel),
            'payment_methods' => $this->paymentMethodsReport($date, $startDate, $endDate, $type, $hotel),
            'channels' => $this->channelsReport($date, $startDate, $endDate, $type, $hotel)
        ];

        return response()->json([
            'salesReport' => $salesReport,
        ]);
    }

        /**
     * Return payment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function saleReport($date, $startDate, $endDate, $type, $hotel, $isDate)
    {
        $start = Carbon::parse($startDate)->copy()->format('Y-m-d');
        $end = Carbon::parse($endDate)->copy()->format('Y-m-d');
        $_date = $date->copy();
        $data = $this->incomeTypesReport($_date, $start, $end, $type, $hotel);
        switch ($type) {
            case 'year':
                $_date = $_date->subYear();
                break;

            case 'month':
                $_date = $_date->subMonth();
                break;

            case 'week':
                if ($isDate) {
                    $_start = Carbon::parse($start);
                    $start = $_start->copy()->subDays(Carbon::parse($_start)->diffInDays(Carbon::parse($end)) + 1)->format('Y-m-d');
                    $end = $_start->subDay()->format('Y-m-d');
                } else {
                    $_date = $_date->subWeek();
                    $start = $_date->startOfWeek()->format('Y-m-d');
                    $end = $_date->endOfWeek()->format('Y-m-d');
                }
                break;

            default:
                $_date = $_date->subDay();
                break;
        }
        $before = $this->incomeTypesReport($_date, $start, $end, $type, $hotel);
        $nowTotal = $data[0]['total'] + $data[1]['total'];
        $beforeTotal = $before[0]['total'] + $before[1]['total'];
        $saleReport = [
            'total' => [
                'now' => $nowTotal,
                'before' => $beforeTotal
            ],
            'services' => [
                'now' => $data[0]['total'],
                'before' => $before[0]['total'],
                'diff' => $before[0]['total'] > 0 ? number_format(($data[0]['total'] - $before[0]['total']) / $before[0]['total'] * 100, 2) : $data[0]['total']
            ],
            'roomTypes' => [
                'now' => $data[1]['total'],
                'before' => $before[1]['total'],
                'diff' => $before[1]['total'] > 0 ? number_format(($data[1]['total'] - $before[1]['total']) / $before[1]['total'] * 100, 2) : $data[1]['total']
            ]
        ];
        return $saleReport;
    }

    /**
     * Return payment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function paymentMethodsReport($date, $startDate, $endDate, $type, $hotel)
    {
        // Loop all payment methods
        $paymentMethods = $hotel->paymentMethods->filter(function ($item, $key) use ($date, $startDate, $endDate, $type) {
            // Payment method's total payment amount
            $item->total = $item->paymentPays()
                ->when($type === 'year', function ($query) use ($date) {
                    $query->whereYear('payment_pays.created_at', $date->year);
                })
                ->when($type === 'month', function ($query) use ($date) {
                    $query->whereMonth('payment_pays.created_at', $date->month);
                })
                ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                    $query->where([
                        ['payment_pays.created_at', '>=', $startDate],
                        ['payment_pays.created_at', '<=', $endDate],
                    ]);
                })
                ->when($type === 'today', function ($query) use ($date) {
                    $query->whereDate('payment_pays.created_at', $date);
                })
                ->sum('amount');

            return $item->total > 0;
        });

        return $paymentMethods->flatten();
    }

    /**
     * Return payment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function incomeTypesReport($date, $startDate, $endDate, $type, $hotel)
    {
        $reservations = Reservation::where('hotel_id', $hotel->id)
            ->whereNotIn('status', ['no-show', 'pending', 'canceled'])
            ->whereRaw('amount = amount_paid')
            ->when($type === 'year', function ($query) use ($date) {
                $query->whereYear('created_at', $date->year);
            })
            ->when($type === 'month', function ($query) use ($date) {
                $query->whereMonth('created_at', $date->month);
            })
            ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                $query->where([
                    ['created_at', '>=', $startDate],
                    ['created_at', '<=', $endDate],
                ]);
            })
            ->when($type === 'today', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })->get();

        $services = 0;

        foreach ($reservations as $item) {
            $services += $item->items->sum(function ($i) {
                return $i['price'] * $i['quantity'];
            });
        }
        // Payment method's total payment amount

        $incomeTypes = [];
        $incomeTypes[] = [
            'total' => $services,
            'name' => 'Нэмэлт үйлчилгээ',
            'color' => '#FB8832',
        ];
        $incomeTypes[] = [
            'total' => $reservations->sum('amount') - $services,
            'name' => 'Өрөө',
            'color' => '#007AFF',
        ];

        return $incomeTypes;
    }

    /**
     * Return payment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function channelsReport($date, $startDate, $endDate, $type, $hotel)
    {
        // Loop all payment methods
        $reservations = $hotel->reservations()
            ->whereNotIn('status', ['no-show', 'pending', 'canceled'])
            ->whereRaw('amount = amount_paid')
            ->when($type === 'year', function ($query) use ($date) {
                $query->whereYear('reservations.created_at', $date->year);
            })
            ->when($type === 'month', function ($query) use ($date) {
                $query->whereMonth('reservations.created_at', $date->month);
            })
            ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                $query->where([
                    ['reservations.created_at', '>=', $startDate],
                    ['reservations.created_at', '<=', $endDate],
                ]);
            })
            ->when($type === 'today', function ($query) use ($date) {
                $query->whereDate('reservations.created_at', $date);
            });

        $sourcesClones = $reservations
            ->join('source_clones AS sc', 'sc.id', '=', 'reservations.source_clone_id')
            ->select('sc.name', 'sc.source_id', DB::raw('sum(reservations.amount) as total'))
            ->groupBy('sc.name')
            ->get();
        $totalSum = $sourcesClones->sum('total');

        $sources = $hotel->sources()
            ->select('id', 'name','short_name', 'color')
            ->get();

        foreach ($sources as $source) {
            if (count($sourcesClones) < 1) {
                $source->total = 0;
                $source->value = 0;
            }
            foreach ($sourcesClones as $sourceClone) {
                if ($sourceClone->source_id === $source->id) {
                    $source->total = $sourceClone->total;
                    $source->value = round($totalSum > 0 ? ($sourceClone->total * 100) / $totalSum : 0);
                } else {
                    $source->total = $source->total === null ? 0 : $source->total;
                    $source->value = round($totalSum > 0 ? ($source->total === null ? 0 : $source->total * 100) / $totalSum : 0);
                }
            }
        }

        return $sources->sortByDesc('total')->flatten();
    }

    /**
     * Return payment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function roomTypesReport($date, $startDate, $endDate, $type, $hotel, $isDate)
    {
        $start = Carbon::parse($startDate)->copy()->format('Y-m-d');
        $end = Carbon::parse($endDate)->copy()->format('Y-m-d');
        $_date = $date->copy();
        $isDate = false;
        $now = $this->reportFormula($_date, $start, $end, $type, $hotel, true);
        $roomTypes = $now['roomTypes'];
        $totalRevenue = $now['totalRevenue'];
        $adr = $now['adr'];
        $revpar = $now['revpar'];
        $maxRoomType = $roomTypes->sortByDesc('total')->first();
        $minRoomType = $roomTypes->sortBy('total')->first();
        $roomTypes = $roomTypes->toArray();
        $max = [
            'name' => $maxRoomType->name,
            'total' => $maxRoomType->total,
        ];
        $min = [
            'name' => $minRoomType->name,
            'total' => $minRoomType->total,
        ];
        switch ($type) {
            case 'year':
                $_date = $_date->subYear();
                break;

            case 'month':
                $_date = $_date->subMonth();
                break;

            case 'week':
                if ($isDate) {
                    $_start = Carbon::parse($start);
                    $start = $_start->copy()->subDays(Carbon::parse($_start)->diffInDays(Carbon::parse($end)) + 1)->format('Y-m-d');
                    $end = $_start->subDay()->format('Y-m-d');
                } else {
                    $_date = $_date->subWeek();
                    $start = $_date->startOfWeek()->format('Y-m-d');
                    $end = $_date->endOfWeek()->format('Y-m-d');
                }
                break;

            default:
                $_date = $_date->subDay();
                break;
        }
        $before = $this->reportFormula($_date, $start, $end, $type, $hotel);
        $roomTypesReport = [
            'total' => [
                'now' => $totalRevenue,
                'before' => $before['totalRevenue']
            ],
            'adr' => [
                'now' => round($adr),
                'before' => round($before['adr']),
                'diff' => round($before['adr']) > 0 ? number_format((round($adr) - round($before['adr'])) / round($before['adr']) * 100, 2) : round($adr)
            ],
            'revpar' => [
                'now' => round($revpar),
                'before' => round($before['revpar']),
                'diff' => round($before['revpar']) > 0 ? number_format((round($revpar) - round($before['revpar'])) / round($before['revpar']) * 100, 2) : round($revpar)
            ],
            'roomTypes' => $roomTypes,
            'min' => $min,
            'max' => $max
        ];

        return $roomTypesReport;
    }

    /**
     * Return given date ADR, revPAR formula data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function reportFormula($date, $startDate = null, $endDate = null, $type, $hotel) {
        $statuses = array('no-show', 'canceled', 'pending');
        // Loop all room types
        $roomTypes = $hotel->roomTypes->map(function ($item, $key) use ($date, $startDate, $endDate, $type, $statuses) {
            // Payment method's total payment amount
            $rooms = $item->roomTypeClones()
                ->when($type === 'year', function ($query) use ($date) {
                    $query->whereYear('created_at', $date->year);
                })
                ->when($type === 'month', function ($query) use ($date) {
                    $query->whereMonth('created_at', $date->month);
                })
                ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                    $query->where([
                        ['created_at', '>=', $startDate],
                        ['created_at', '<=', $endDate],
                    ]);
                })
                ->when($type === 'today', function ($query) use ($date) {
                    $query->whereDate('created_at', $date);
                })
                ->get();
            $item->availableRoomsCount = $item->rooms()->count();
            $number = $rooms->reduce(function ($number, $item) use ($statuses) {
                return $number + (in_array($item->reservation->status, $statuses) == false && $item->reservation->amount === $item->reservation->amount_paid) ? 1 : 0;
            });
            // $availableRoomsCount = $rooms->reduce(function ($availableRoomsCount, $item) {
            //     return $availableRoomsCount + $item->reservation->allAvailableRoomsCount();
            // });
            $total = $rooms->reduce(function ($total, $item) use ($statuses) {
                return $total + ((in_array($item->reservation->status, $statuses) == false && $item->reservation->amount === $item->reservation->amount_paid) ? $item->reservation->amount : 0);
            });
            $item->total = $total ? $total : 0;
            $item->date = [$startDate, $endDate];
            $item->number = $number ? $number : 0;
            // $item->availableRoomsCount = $availableRoomsCount ? $availableRoomsCount : 0;
            return $item;
        });
        $totalRevenue = $roomTypes->sum('total');
        $numbers = $roomTypes->sum('number');
        if ($totalRevenue === 0) {
            $adr = 0;
            $revpar = 0;
        } else {
            $adr = $totalRevenue / $numbers;
            $revpar = $totalRevenue / $roomTypes->sum('availableRoomsCount');
        }
        $data = [
            'roomTypes' => $isRoomType = true ? $roomTypes : [],
            'totalRevenue' => $totalRevenue,
            'adr' => $adr,
            'revpar' => $revpar,
            'date' => $date
        ];
        return $data;
    }

    public function reservationReport(Request $request)
    {
        // Validation
        $request->validate([
            'type' => 'required|string|in:today,week,month,year,day',
            'date' => 'required|array'
        ]);

        $type = $request->query('type');
        $hotel = request()->hotel;
       $date = Carbon::parse($hotel->working_date);
        $startDate = $endDate = $date;
        $isDate = false;

        if ($type === 'week') {
            $startDate = $date->startOfWeek()->format('Y-m-d');
            $endDate = $date->endOfWeek()->format('Y-m-d');
        }
        if (!($request->query('date', null)[0] === null && $request->query('date', null)[1] === null)) {
            $startDate = $request->query('date', null)[0];
            $endDate = $request->query('date', null)[1];
            $isDate = true;
            $type = 'week';
        }

        $start = Carbon::parse($startDate)->copy()->format('Y-m-d');
        $end = Carbon::parse($endDate)->copy()->format('Y-m-d');
        $_date = $date->copy();
        switch ($type) {
            case 'year':
                $_date = $_date->subYear();
                break;

            case 'month':
                $_date = $_date->subMonth();
                break;

            case 'week':
                if ($isDate) {
                    $_start = Carbon::parse($start);
                    $start = $_start->copy()->subDays(Carbon::parse($_start)->diffInDays(Carbon::parse($end)) + 1)->format('Y-m-d');
                    $end = $_start->subDay()->format('Y-m-d');
                } else {
                    $_date = $_date->subWeek();
                    $start = $_date->startOfWeek()->format('Y-m-d');
                    $end = $_date->endOfWeek()->format('Y-m-d');
                }
                break;

            default:
                $_date = $_date->subDay();
                break;
        }

        $reservationReport = [
            'percentage' => [
                'current' => $this->percentageData($date, $startDate, $endDate, $type, $hotel),
                'before' => $this->percentageData($_date, $start, $end, $type, $hotel),
            ],
            'day' => $this->dayData($date, $startDate, $endDate, $type, $hotel),
            'rooms' => $this->roomsData($date, $startDate, $endDate, $type, $hotel)
        ];

        return response()->json([
            'reservationReport' => $reservationReport,
        ]);
    }

    /**
     * Return given date percentage of room filling data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function dayData($date, $startDate = null, $endDate = null, $type, $hotel) {
        $reservations = $hotel->reservations()
            ->when($type === 'year', function ($query) use ($date) {
                $query->whereYear('check_in', $date->year);
            })
            ->when($type === 'month', function ($query) use ($date) {
                $query->whereMonth('check_in', $date->month);
            })
            ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                $query->where([
                    ['check_in', '>=', $startDate],
                    ['check_in', '<=', $endDate],
                ]);
            })
            ->when($type === 'today', function ($query) use ($date) {
                $query->whereDate('check_in', $date)->orWhereDate('check_out', $date);
            })
            ->get();
        $data = [];
        if ($type === 'today') {
            $checkIn = $reservations->where('check_in', '>=', $date->format('Y-m-d'))->whereIn('status', ['pending', 'confirmed', 'checked-in'])->count();
            $checkOut = $reservations->where('check_in', '<=', $date->format('Y-m-d'))->whereIn('status', ['pending', 'confirmed', 'checked-in', 'checked-out'])->count();
            $noShow = $reservations->where('status', 'no-show')->count();
            $checkedIn = $reservations->where('check_in', '>=', $date->format('Y-m-d'))->where('status', 'checked-in')->count();
            $checkedOut = $reservations->where('check_in', '<=', $date->format('Y-m-d'))->where('status', 'checked-out')->count();
            $cancelled = $reservations->where('status', 'canceled')->count();
            $averageStay = $reservations->map(function ($item, $key) {
                return $item->getStayNightsAttribute();
            })->avg();
            $guests = $reservations->map(function ($item, $key) {
                return $item->guestClones->count();
            })->sum();
            $data = [
                'checkIn' => $checkIn,
                'checkedIn' => $checkedIn,
                'checkOut' => $checkOut,
                'checkedOut' => $checkedOut,
                'noShow' => $noShow,
                'cancelled' => $cancelled,
                'averageStay' => number_format($averageStay, 2),
                'guests' => $guests
            ];
        } else {
            $checkIn = $reservations->whereIn('status', ['pending', 'confirmed', 'checked-in'])->count();
            $checkOut = $reservations->whereIn('status', ['pending', 'confirmed', 'checked-in', 'checked-out'])->count();
            $noShow = $reservations->where('status', 'no-show')->count();
            $cancelled = $reservations->where('status', 'canceled')->count();
            $guests = $reservations->map(function ($item, $key) {
                return $item->guestClones->count();
            })->sum();
            $averageStay = $reservations->map(function ($item, $key) {
                return $item->getStayNightsAttribute();
            })->avg();
            $data = [
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'noShow' => $noShow,
                'cancelled' => $cancelled,
                'averageStay' => number_format($averageStay, 2),
                'date' => $date->year,
                'guests' => $guests
            ];
        }

        return $data;
    }

    /**
     * Return given date percentage of occupancy data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function percentageData($date, $startDate = null, $endDate = null, $type, $hotel) {
        $total = $hotel->rooms()->count();
        $count = $hotel->reservations()
            ->whereNotIn('status', ['pending', 'canceled', 'no-show', 'confirmed'])
            ->when($type === 'year', function ($query) use ($date) {
                $query->whereYear('check_in', $date->year);
            })
            ->when($type === 'month', function ($query) use ($date) {
                $query->whereMonth('check_in', $date->month);
            })
            ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                $query->where([
                    ['check_in', '>=', $startDate],
                    ['check_in', '<=', $endDate],
                ]);
            })
            ->when($type === 'today', function ($query) use ($date) {
                $query->whereDate('check_in', $date)->orWhereDate('check_out', $date);
            })
            ->count();

        return number_format($count / $total * 100, 2);
    }

    /**
     * Return given date percentage of room filling data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function roomsData($date, $startDate = null, $endDate = null, $type, $hotel) {
        $start = Carbon::parse($startDate)->copy()->format('Y-m-d');
        $end = Carbon::parse($endDate)->copy()->format('Y-m-d');
        $_date = $date->copy();

        switch ($type) {
            case 'year':
                $start = $_date->startOfYear();
                $end = $_date->endOfYear();
                break;

            case 'month':
                $start = $_date->startOfMonth();
                $end = $_date->endOfMonth();
                break;

            case 'today':
                $start = $_date->startOfDay();
                $end = $_date->endOfDay();
                break;

            default:
                break;
        }
        $data = $hotel->roomTypes->map(function ($item, $key) use ($date, $start, $end, $type) {
            $item->rooms_count = $item->rooms()->count();
            $item->rooms_available = $item->availableRoomsCount($start, $end);
            // $item->blocks_count = $item->availableRoomsCount();
            // $item->saled_count = $item->availableRoomsCount();
            // $item->percent = $item->availableRoomsCount();
            return $item;
        });

        return $data;
    }
}
