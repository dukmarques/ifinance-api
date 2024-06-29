<?php

use Carbon\Carbon;

if (! function_exists('createCarbonDateFromString')) {
    function createCarbonDateFromString(string|null $dateString): Carbon {
        return $dateString ? Carbon::parse($dateString) : Carbon::now();
    }
}

if (!function_exists('isSameMonthAndYear')) {
    function isSameMonthAndYear(Carbon|string $date1, Carbon|string $date2): bool {
        if (is_string($date1)) {
            $date1 = Carbon::parse($date1);
        }

        if (is_string($date2)) {
            $date2 = Carbon::parse($date2);
        }

        return $date1->format('Y-m') === $date2->format('Y-m');
    }
}

if (!function_exists('isDateGreaterThan')) {
    function isDateGreaterThan(Carbon|string $date1, Carbon|string $date2): bool {
        if (is_string($date1)) {
            $date1 = Carbon::parse($date1);
            $date1->startOfMonth();
        }

        if (is_string($date2)) {
            $date2 = Carbon::parse($date2);
            $date2->startOfMonth();
        }

        return $date1->greaterThan($date2);
    }
}

