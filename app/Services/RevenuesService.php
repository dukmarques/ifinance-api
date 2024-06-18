<?php

namespace App\Services;

use App\Models\Revenues;
use App\Models\RevenuesOverrides;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class RevenuesService
{
    public function index($requestData): Collection|array {
        $date = createCarbonDateFromString($requestData['date']);

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
        $date = createCarbonDateFromString(request()->input('date'));

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

        $date = createCarbonDateFromString(Arr::get($data, 'date'));

        // atualizar apenas mês atual: criar RevenueOverride
        if ($revenue->recurrent && $data['update_type'] === Revenues::ONLY_MONTH) {
            $this->handleUpdateOnlyMonthInformed(attributes: $data, revenue: $revenue, date: $date);
            return $revenue->with([
                'overrides' => function ($query) use ($date) {
                    $query->whereMonth('revenues_overrides.receiving_date', $date->month)
                        ->whereYear('revenues_overrides.receiving_date', $date->year);
                }]
            )->first();
        }

        // atualizar mês atual e próximos adiante: adicionar deprecated e criar novo registro
        if (
                $revenue->recurrent
                && $data['update_type'] === Revenues::CURRENT_MONTH_AND_FOLLOWERS
                && !isSameMonthAndYear($date, $revenue->receiving_date)
        ) {
            return $this->handleUpdateInformedMonthAndFollowing(attributes: $data, revenue: $revenue, date: $date);
        }

        $revenue->update($data);
        return $revenue;
    }

    private function handleUpdateOnlyMonthInformed(Array $attributes, Revenues $revenue, Carbon $date) {
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

    private function handleUpdateInformedMonthAndFollowing(Array $attributes, Revenues $revenue, Carbon $date) {
        $newRevenue = $revenue->replicate()->fill(
            $attributes + [
                'receiving_date' => $date->toDateString(),
            ]
        );
        $newRevenue->save();

        $revenue->deprecated_date = $date->toDateString();
        $revenue->save();

        return $newRevenue;
    }

    public function destroy(string $id): bool|null {
        $revenue = Revenues::query()->find($id);

        if (!$revenue) {
            return null;
        }

        $date = createCarbonDateFromString(request()->input('date'));
        $type = request()->input('exclusion_type') ?: null;

        // excluir apenas mês atual
        if ($revenue->recurrent && $type === Revenues::ONLY_MONTH) {
            return $this->deleteOnlyInCurrentMonth(revenue: $revenue, date: $date);
        }

        // excluir mês atual e próximos
        if (
            $revenue->recurrent
            && $type === Revenues::CURRENT_MONTH_AND_FOLLOWERS
            && !isSameMonthAndYear($date, $revenue->receiving_date)
        ) {
            return $this->deleteInCurrentAndUpcomingMonths(revenue: $revenue, date: $date);
        }

        return $revenue->forceDelete();
    }

    private function deleteInCurrentAndUpcomingMonths(Revenues $revenue, Carbon $date, ): bool
    {
        return $revenue->update([
            'deprecated_date' => $date->subMonths(1)->toDateString(),
        ]);
    }

    private function deleteOnlyInCurrentMonth(Revenues $revenue, Carbon $date): bool {
        $override = $revenue->overrides()->whereMonth('receiving_date', '=', $date->month)
                        ->whereYear('receiving_date', '=', $date->year)
                        ->first();

        if ($override) {
            $override->is_deleted = true;
            return $override->save();
        }

        return RevenuesOverrides::query()->create([
            'revenues_id' => $revenue->id,
            'receiving_date' => $date->toDateString(),
            'is_deleted' => true,
        ])->save();
    }
}
