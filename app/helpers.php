<?php

use Carbon\Carbon;

if (! function_exists('createCarbonDateFromString')) {
    function createCarbonDateFromString(string|null $dateString): Carbon {
        return $dateString ? Carbon::parse($dateString) : Carbon::now();
    }
}
