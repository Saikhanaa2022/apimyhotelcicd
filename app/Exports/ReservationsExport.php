<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\{WithHeadings, WithMapping, FromCollection};

class ReservationsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Export items.
     *
     * @var array
     */
    protected $items;
    
    /**
     * Instantiate a new export instance.
     *
     * @return void
     */
    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            //
        ];
    }

    public function map($item): array
    {
        return [
            //
        ];
    }
}
