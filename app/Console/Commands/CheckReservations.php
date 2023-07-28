<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check reservation dates then notify to managers';

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
        // Check reservations checkout then notify to manager before 30 minutes
        dd('Check reservation checkout date');
    }
}
