<?php

namespace App\Services;

use App\Models\Revenues;
use App\Models\RevenuesOverrides;
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
        return Revenues::query()->create($data);
    }

    public function update(string $id, Array $data): Revenues|null {
        $revenue = Revenues::find($id);

        if (!$revenue) {
            return null;
        }

        // TODO: atualizar apenas mês atual: criar RevenueOverride
        if ($revenue->recurrent && $data['update_type'] === 'only_month') {
            $date = Carbon::parse($data['date']);

            $this->handleUpdateOnlyMonthInformed(attributes: $data, revenue: $revenue);
            return $revenue->with([
                'overrides' => function ($query) use ($date) {
                    $query->whereMonth('revenues_overrides.receiving_date', $date->month)
                        ->whereYear('revenues_overrides.receiving_date', $date->year);
                }]
            )->first();
        }

        // TODO: atualizar mês atual e próximos adiante: adicionar deprecated e criar novo registro
        if ($revenue->recurrent && $data['update_type'] === '') {
            return $this->handleUpdateInformedMonthAndFollowing();
        }

        $revenue->update($data);
        return $revenue;
    }

    private function handleUpdateOnlyMonthInformed(Array $attributes, Revenues $revenue) {
        $date = Carbon::parse($attributes['date']);

        $revenueOverride = RevenuesOverrides::query()
            ->where('revenues_id', $revenue->id)
            ->whereMonth('revenues_overrides.receiving_date', '=', $date->month)
            ->whereYear('revenues_overrides.receiving_date', '=', $date->year)
            ->first();

        if (!$revenueOverride) {
            return RevenuesOverrides::query()->create([
                'title' => $attributes['title'] ?: null,
                'amount' => $attributes['amount'] ?: null,
                'receiving_date' => $date,
                'description' => $attributes['description'] ?: null,
                'revenues_id' => $revenue->id,
            ]);
        }

        return $revenueOverride->update($attributes);
    }

    private function handleUpdateInformedMonthAndFollowing() {}
}
