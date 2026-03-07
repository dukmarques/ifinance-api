<?php

namespace App\Traits;

trait HasRecurrentTypes
{
    const EDIT_TYPE_ONLY_MONTH = 'only_month';
    const EDIT_TYPE_CURRENT_AND_FUTURE = 'current_and_future';
    const EDIT_TYPE_ALL = 'all';

    const DELETE_TYPE_ONLY_MONTH = 'only_month';
    const DELETE_TYPE_CURRENT_AND_FUTURE = 'current_and_future';
    const DELETE_TYPE_ALL = 'all';

    public static function getEditTypes(): array
    {
        return [
            self::EDIT_TYPE_ONLY_MONTH,
            self::EDIT_TYPE_CURRENT_AND_FUTURE,
            self::EDIT_TYPE_ALL,
        ];
    }

    public static function getDeleteTypes(): array
    {
        return [
            self::DELETE_TYPE_ONLY_MONTH,
            self::DELETE_TYPE_CURRENT_AND_FUTURE,
            self::DELETE_TYPE_ALL,
        ];
    }
}
