<?php

namespace App\Services;

use App\Models\Revenues;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RevenuesService
{
    public function index($requestData): Collection|array {
        $date = isset($requestData['date'])
            ? Carbon::parse($requestData['date'])
            : Carbon::now();

        $query = Revenues::query()
            ->whereNot('revenues.deprecated', '=', true)
            ->orWhere('revenues.deprecated', '=', true)
            ->whereDate('revenues.receiving_date', '<=', $date)
            ->with([
                'category',
                'overrides' => function ($query) use ($date) {
                    $query->whereMonth('revenues_overrides.receiving_date', $date->month)
                        ->whereYear('revenues_overrides.receiving_date', $date->year);
                }
            ]);

        return $query->get();
    }

    public function show(string $id)
    {
        $date = request()->has('date')
            ? Carbon::parse(request()->input('date'))
            : Carbon::now();

        return Revenues::with([
            'category',
            'overrides' => function ($query) use ($date) {
                $query->whereMonth('revenues_overrides.receiving_date', $date->month)
                    ->whereYear('revenues_overrides.receiving_date', $date->year);
            }
        ])->find($id);
    }
}
