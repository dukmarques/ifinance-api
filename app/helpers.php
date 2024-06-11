<?php

use Carbon\Carbon;

if (! function_exists('createCarbonDateFromString')) {
    function createCarbonDateFromString(string $dateString): Carbon {
        return $dateString ? Carbon::parse($dateString) : Carbon::now();
    }
}
