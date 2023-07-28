<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hotel;
use Carbon\Carbon;

class CheckNightAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:night-audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check hotels night audit time then perform.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get check date
        $checkDate = Carbon::today();
        // Get check time
        // $checkTime = Carbon::now()->format('H:i');

        // Get active hotels
        // $hotels = Hotel::where('is_active', true)
        //     ->where('working_date', '<', $checkDate)
        //     ->whereHas('hotelSetting', function ($query) use ($checkTime) {
        //         $query
        //             ->where('has_night_audit', true)
        //             ->where('is_nightaudit_auto', true)
        //             ->where('night_audit_time', '<=', $checkTime);
        //     })
        //     ->get();

        // Get is not perform night audit hotels
        $hotels = Hotel::where('is_active', true)
            ->where('working_date', '<', $checkDate)
            ->whereHas('hotelSetting', function ($query) {
                $query->where('has_night_audit', false);
            })
            ->get();
        $countAudited = 0;

        // Perform night audit on filtered hotels
        foreach ($hotels as $hotel) {
            $hotel->working_date = $checkDate;
            $hotel->update();
            $countAudited++;
        }

        dd('Done: ' . $countAudited);
    }
}
