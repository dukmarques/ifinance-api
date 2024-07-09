<?php

namespace App\Traits;

trait HasEditTypes
{
    const EDIT_TYPE_ONLY_MONTH = 'only_month';
    const EDIT_TYPE_CURRENT_AND_FUTURE = 'current_and_future';
    const EDIT_TYPE_ALL = 'all';

    public static array $editTypes = [
        self::EDIT_TYPE_ONLY_MONTH,
        self::EDIT_TYPE_CURRENT_AND_FUTURE,
        self::EDIT_TYPE_ALL,
    ];
}
