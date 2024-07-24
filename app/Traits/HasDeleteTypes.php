<?php

namespace App\Traits;

trait HasDeleteTypes
{
    const DELETE_TYPE_SIMPLE = 'simple';
    const DELETE_TYPE_ONLY_MONTH = 'only_month';
    const DELETE_TYPE_CURRENT_AND_FUTURE = 'current_and_future';
    const DELETE_TYPE_ALL = 'all';

    public static array $deleteTypes = [
        self::DELETE_TYPE_SIMPLE,
        self::DELETE_TYPE_ONLY_MONTH,
        self::DELETE_TYPE_CURRENT_AND_FUTURE,
        self::DELETE_TYPE_ALL,
    ];
}
