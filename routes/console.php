<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('generate-slug {id?}', function ($id = NULL) {
    if ($id) {
        // Find hotel
        $hotel = \App\Models\Hotel::findOrFail($id);
        if (!$hotel->slug) {
            // Generate random str
            $uniqueStr = substr(md5(uniqid(mt_rand())), 0, 6);
            $hotel->slug = $uniqueStr;
            $hotel->save();
        }

        dd($hotel->slug);
    } else {
        $hotels = \App\Models\Hotel::all();
        foreach ($hotels as $h) {
            $h->slug = substr(md5(uniqid(mt_rand())), 0, 6);
            $h->save();
        }

        dd(count($hotels));
    }
})->describe('Generate slug for hotel');

Artisan::command('logs:clear', function () {
    exec('rm ' . storage_path('logs/*.log'));

    $this->comment('Logs have been cleared!');
})->describe('Clear log files');

Artisan::command('generate-token', function () {
    $key = Str::random(10);
    $token = hash('sha256', $key);
    dd($token);
})->describe('Generate token');
