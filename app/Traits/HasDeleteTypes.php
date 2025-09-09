<?php

namespace App\Traits;

trait HasDeleteTypes
{
    public const DELETE_TYPE_ONLY_MONTH = 'only_month';
    public const DELETE_TYPE_CURRENT_AND_FUTURE = 'current_and_future';
    public const DELETE_TYPE_ALL = 'all';

    public static array $deleteTypes = [
        self::DELETE_TYPE_ONLY_MONTH,
        self::DELETE_TYPE_CURRENT_AND_FUTURE,
        self::DELETE_TYPE_ALL,
    ];
}
