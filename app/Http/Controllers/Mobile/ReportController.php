<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Return payment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentMethodsReport(Request $request)
    {
        // Validation
        $request->validate([
            'type' => 'required|string|in:today,week,month,year',
        ]);

        $type = $request->query('type');
        $hotel = request()->hotel;

        $date = Carbon::parse($hotel->working_date);
        $startDate = $endDate = $date;

        if ($type === 'week') {
            $startDate = $date->startOfWeek()->format('Y-m-d');
            $endDate = $date->endOfWeek()->format('Y-m-d');
        } else if ($type === 'month') {
            $startDate = $date->startOfMonth()->format('Y-m-d');
            $endDate = $date->endOfMonth()->format('Y-m-d');
        } else if ($type === 'year') {
            $month = [];
            for ($m=1; $m<=12; $m++) {
                 $month[] = date('F', mktime(0,0,0,$m, 1, date('Y')));
            }
        }

        // Loop all payment methods
        $paymentMethods = $hotel->paymentMethods->map(function ($item, $key) use ($date, $startDate, $endDate, $type) {
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

            return $item;
        });

        // calculate rooms average price
        $reservations = $hotel->reservations()
            ->when($type === 'year', function ($query) use ($date) {
                $query->whereYear('check_in', $date->year)->where([
                    ['status', '<>', 'canceled'],
                    ['status', '<>', 'pending'],
                ])->with('items')->with('taxClones');
            })
            ->when($type === 'month', function ($query) use ($date) {
                $query->whereMonth('check_in', $date->month)->where([
                    ['status', '<>', 'canceled'],
                    ['status', '<>', 'pending'],
                ])->with('items')->with('taxClones');
            })
            ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                $query->where([
                    ['check_in', '<=', $endDate],
                    ['check_out', '>=', $startDate],
                    ['status', '<>', 'canceled'],
                    ['status', '<>', 'pending'],
                ])->with('items')->with('taxClones');
            })
            ->when($type === 'today', function ($query) use ($date) {
                $query->whereDate('check_in', $date)->where([
                    ['status', '<>', 'canceled'],
                    ['status', '<>', 'pending'],
                ])->with('items')->with('taxClones');
            })->get();   

        $roomIncome = 0;
        $additional_service = 0;
        foreach ($reservations as $reservation) {
            $computed = 0;
            foreach ($reservation->items as $item) {
                $computed += $reservation->amount_paid > 0 ? $item->amount : 0;
            }

            // calculate tax
            foreach ($reservation->taxClones as $tax) {
                if ($tax->is_default != 1) {
                    $computed += $computed / 100 * $tax->percentage;
                }
            }

            $roomIncome += $reservation->amount_paid - $computed;
            $additional_service += $computed;
        }

        $totalAmount = 0;
        if ($type === 'year') { 
            for ($i = 0; $i < count($month); $i++) {
                $start = Carbon::parse($month[$i])->startOfMonth()->format('Y-m-d');
                $end = Carbon::parse($start)->endOfMonth()->format('Y-m-d');

                $countAmount = 0;
                foreach ($reservations as $reservation) {
                    $todayIncome = $reservation->payments()
                        ->whereMonth('payments.posted_date', [Carbon::parse($start)->month])
                        ->where('income_type', '!=', 'receivable')->get();
                    $countAmount += $todayIncome->sum('amount');
                }

                // $paymentPercents[''.$start] =  $todayIncome->sum('amount');

                $paymentPercents[] =  [
                    'date' => $start,
                    'value' => $countAmount
                ];
                $totalAmount += $countAmount;
            }
        } else {
            $nights = stayNights($startDate, $endDate, false);
            for ($i = 0; $i <= $nights; $i++) {
                $start = Carbon::parse($startDate)->addDays($i)->format('Y-m-d');
                $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');

                $countAmount = 0;
                foreach ($reservations as $reservation) {
                    $todayIncome = $reservation->payments
                        ->where('income_type', '!=', 'receivable')
                        ->where('posted_date', $start)
                        ->sum('amount');
                    $countAmount += $todayIncome;
                }

                $paymentPercents[] =  [
                    'date' => $start,
                    'value' => $countAmount
                ];
                $totalAmount += $countAmount;
            }
        }

        $incomeTypes[] = [
            'name' => 'room',
            'value' =>  number_format($roomIncome,0,'.',''),
            'color' => '#111827'
        ];

        $incomeTypes[] = [
            'name' => 'additional_service',
            'value' =>  number_format($additional_service,0,'.',''),
            'color' => '#F59E0B'
        ];

        return response()->json([
            // 'res' => $reservations,
            'totalAmount' => $totalAmount,
            'daysAmount' => $paymentPercents,
            'incomeTypes' => $incomeTypes,
            'paymentMethods' => $paymentMethods,
            "roomAveragePrice" => count($reservations) === 0 ? 0 : number_format($roomIncome / count($reservations),0,'.','')
        ]);
    }

    /**
     * Return reservation report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reservationReport(Request $request)
    {
        // Validation
        $request->validate([
            'type' => 'required|string|in:today,week,month,year',
        ]);

        $type = $request->query('type');
        $hotel = request()->hotel;

        $date = Carbon::parse($hotel->working_date);
        $startDate = $endDate = $date;

        if ($type === 'week') {
            $startDate = $date->startOfWeek()->format('Y-m-d');
            $endDate = $date->endOfWeek()->format('Y-m-d');
        } else if ($type === 'month'){
            $startDate = $date->startOfMonth()->format('Y-m-d');
            $endDate = $date->endOfMonth()->format('Y-m-d');
        } else if ($type === 'year') {
            $month = [];
            for ($m=1; $m<=12; $m++) {
                 $month[] = date('F', mktime(0,0,0,$m, 1, date('Y')));
            }
        }

        $rooms = $hotel->rooms();
        $totalRooms = $rooms->count();

        if ($type === 'year') { 
            for ($i = 0; $i < count($month); $i++) {
                $start = Carbon::parse($month[$i])->startOfMonth()->format('Y-m-d');
                $end = Carbon::parse($start)->endOfMonth()->format('Y-m-d');
                $unassigned = $rooms->unassigned($start, $end)->count();
                // $roomsPercent[''.$start] =  round((($totalRooms - $unassigned) * 100) / $totalRooms, 0);
                $roomsPercent[] =  [
                    'date' => $start,
                    'value' => round((($totalRooms - $unassigned) * 100) / $totalRooms, 0)
                ];
            }
        } else {
            $nights = stayNights($startDate, $endDate, false);
            for ($i = 0; $i <= $nights; $i++) {
                $start = Carbon::parse($startDate)->addDays($i)->format('Y-m-d');
                $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
    
                $unassigned = $rooms->unassigned($start, $end)->count();
    
                // $roomsPercent[''.$start] =  round((($totalRooms - $unassigned) * 100) / $totalRooms, 0);
                $roomsPercent[] =  [
                    'date' => $start,
                    'value' => round((($totalRooms - $unassigned) * 100) / $totalRooms, 0)
                ];
            }
        }

        $statuses = $this->filterReservations($hotel, $type, $date, $startDate, $endDate)
            ->select('status', DB::raw('count(status) as total'))
            ->groupBy('status')
            ->get();

        $sourcesClones = $this->filterReservations($hotel, $type, $date, $startDate, $endDate)
            ->leftJoin('source_clones AS sc', 'sc.id', '=', 'reservations.source_clone_id')
            ->select(DB::raw('count(sc.name) as total_source'), 'sc.name', 'sc.source_id')
            ->groupBy('sc.name')
            ->get();

        $sources = $hotel->sources()
            ->select('id', 'name','short_name')
            ->get();

        foreach ($sourcesClones as $sourceClone) {
            foreach ($sources as $source) {
                if ($sourceClone->source_id === $source->id) {
                    $source->total = $sourceClone->total_source;
                } else {
                    $source->total = $source->total === null ? 0 : $source->total;
                }
            }
        }

        return response()->json([
            'assigned_percent' => $roomsPercent,
            'status' => $statuses,
            'sources' => $sources
        ]);
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterReservations($model, $type, $date, $startDate, $endDate )
    {
        return $hotel = $model->reservations()
            ->when($type === 'year', function ($query) use ($date) {
                $query->whereYear('check_in', $date->year);
            })
            ->when($type === 'month', function ($query) use ($date) {
                $query->whereMonth('check_in', $date->month);
            })
            ->when($type === 'week', function ($query) use ($startDate, $endDate) {
                $query->where([
                    ['check_in', '<=', $endDate],
                    ['check_out', '>=', $startDate],
                ]);
            })
            ->when($type === 'today', function ($query) use ($date) {
                $query->whereDate('check_in', $date);
            });
    }
}
