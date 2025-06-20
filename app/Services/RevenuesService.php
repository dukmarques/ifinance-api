<?php

namespace App\Services;

use App\Http\Resources\RevenuesResource;
use App\Models\Revenues;
use App\Models\RevenuesOverrides;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class RevenuesService
{
    public function index($requestData): Collection|array
    {
        $date = createCarbonDateFromString($requestData['date']);

        $query = Revenues::query()
            ->where(function (Builder $query) use ($date) {
                $this->buildRecurringRevenuesQuery($query, $date);
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

        return RevenuesResource::collection($query->get())->response()->getData(true);
    }

    private function buildRecurringRevenuesQuery(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('receiving_date', '<=', $date)
            ->where(function ($subQuery) use ($date) {
                $subQuery->whereDate('deprecated_date', '>=', $date)
                    ->orWhereNull('deprecated_date');
            })
            ->where('recurrent', '=', true);
    }

    public function show(string $id): RevenuesResource|null
    {
        $date = createCarbonDateFromString(request()->input('date'));

        $revenue = Revenues::with([
            'category',
            'overrides' => function ($query) use ($date) {
                $query->whereMonth('revenues_overrides.receiving_date', $date->month)
                    ->whereYear('revenues_overrides.receiving_date', $date->year);
            }
        ])->find($id);

        if (!$revenue) {
            return null;
        }

        return new RevenuesResource($revenue);
    }

    public function store(array $data): RevenuesResource
    {
        $data['user_id'] = Auth::id();
        $revenue = Revenues::query()->create($data);
        return new RevenuesResource($revenue);
    }

    public function update(string $id, array $data): RevenuesResource|null
    {
        $revenue = Revenues::find($id);

        if (!$revenue) {
            return null;
        }

        $date = createCarbonDateFromString(Arr::get($data, 'date'));

        // atualizar apenas mês atual: criar RevenueOverride
        if ($revenue->recurrent && $data['update_type'] === Revenues::ONLY_MONTH) {
            $this->handleUpdateOnlyMonthInformed(attributes: $data, revenue: $revenue, date: $date);
            $revenue = $revenue->with(
                [
                'overrides' => function ($query) use ($date) {
                    $query->whereMonth('revenues_overrides.receiving_date', $date->month)
                        ->whereYear('revenues_overrides.receiving_date', $date->year);
                }]
            )->first();

            return new RevenuesResource($revenue);
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
        return new RevenuesResource($revenue);
    }

    private function handleUpdateOnlyMonthInformed(array $attributes, Revenues $revenue, Carbon $date)
    {
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

    private function handleUpdateInformedMonthAndFollowing(array $attributes, Revenues $revenue, Carbon $date): RevenuesResource
    {
        $newRevenue = $revenue->replicate()->fill(
            $attributes + [
                'receiving_date' => $date->toDateString(),
            ]
        );
        $newRevenue->save();

        $revenue->deprecated_date = $date->toDateString();
        $revenue->save();

        return new RevenuesResource($newRevenue);
    }

    public function destroy(string $id): bool|null
    {
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

    private function deleteInCurrentAndUpcomingMonths(Revenues $revenue, Carbon $date): bool
    {
        return $revenue->update([
            'deprecated_date' => $date->subMonths(1)->toDateString(),
        ]);
    }

    private function deleteOnlyInCurrentMonth(Revenues $revenue, Carbon $date): bool
    {
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
