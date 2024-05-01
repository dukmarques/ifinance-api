<?php

namespace App\Services;

use App\Models\Revenues;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class RevenuesService
{
    public function index($requestData): Collection|array {
        $date = isset($requestData['date'])
            ? Carbon::parse($requestData['date'])
            : Carbon::now();

        $query = Revenues::query()
            ->where(function (Builder $query) use ($date) {
                $query->whereDate('receiving_date', '<=', $date)
                    ->whereDate('deprecated_date', '>=', $date)
                    ->orWhereNull('deprecated_date')
                    ->where('recurrent', '=', true);
            })
            ->orWhere(function (Builder $query) use ($date) {
                $query->whereMonth('receiving_date', $date->month)
                    ->whereYear('receiving_date', $date->year)
                    ->where('recurrent', '=', false);
        })
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

    public function store(Array $data): Revenues|null {
        $data['user_id'] = Auth::id();
        return Revenues::create($data);
    }
}
