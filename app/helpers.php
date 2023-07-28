<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

function snakeCaseKeys(array $data)
{
    $array = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $value = snakeCaseKeys($value);
        }

        $array[snake_case($key)] = $value;
    }

    return $array;
}

function camelCaseKeys(array $data)
{
    $array = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $value = camelCaseKeys($value);
        }

        $array[camel_case($key)] = $value;
    }

    return $array;
}

function getDatesFromRange($from, $to, $days = [])
{
    $dates = \Carbon\CarbonPeriod::create($from, $to)
        ->toArray();

    if (count($days) > 0) {
        $dates = collect($dates)->filter(function ($date) use ($days) {
            return in_array(strtolower(dayOfWeek($date)), $days);
        })->toArray();
    }

    return $dates;
}

function stayNights($from, $to, $isTime = false, $exact = false)
{
    if ($exact) {
        if ($isTime) {
            // Calc diff with minutes
            $diffInMinutes = Carbon::parse($from)->diffInMinutes(Carbon::parse($to)) % 60;
            $diffInHours = Carbon::parse($from)->diffInHours(Carbon::parse($to));
            $diff = $diffInHours . ' цаг ' . $diffInMinutes . ' минут';
        } else {
            // Calc diff with days
            $diff = Carbon::parse($from)->diffInDays(Carbon::parse($to));
        }
        return $diff;
    } else {
        if ($isTime) {
            $diff = Carbon::parse($from)->diffInHours(Carbon::parse($to));
        } else {
            $diff = Carbon::parse(Carbon::parse($from)->format('Y-m-d'))->diffInDays(Carbon::parse(Carbon::parse($to)->format('Y-m-d')));
            // $diff = Carbon::parse($from)->diffInDays(Carbon::parse($to));
        }

        return $diff;
    }
}

// Calc diff between two dates with minutes
function stayMinutes($from, $to) {
    return Carbon::parse($from)->diffInMinutes(Carbon::parse($to));
}

function dayOfWeek($date, $locale = 'en')
{
    App::setLocale($locale);

    switch (Carbon::parse($date)->dayOfWeek) {
        case Carbon::MONDAY:
            return trans_choice('days.monday', $locale);
        case Carbon::TUESDAY:
            return trans_choice('days.tuesday', $locale);
        case Carbon::WEDNESDAY:
            return trans_choice('days.wednesday', $locale);
        case Carbon::THURSDAY:
            return trans_choice('days.thursday', $locale);
        case Carbon::FRIDAY:
            return trans_choice('days.friday', $locale);
        case Carbon::SATURDAY:
            return trans_choice('days.saturday', $locale);
        case Carbon::SUNDAY:
            return trans_choice('days.sunday', $locale);
    }
}

function afterLast($value, $str)
{
    $occurrence = Str::random(40);
    $newValue = Str::replaceLast($str, $occurrence, $value);

    return Str::after($newValue, $occurrence);
}

function beforeLast($value, $str)
{
    $occurrence = Str::random(40);
    $newValue = Str::replaceLast($str, $occurrence, $value);

    return Str::before($newValue, $occurrence);
}

function calculatePercent($value, $percent) {
    return $value * $percent / 100;
}
