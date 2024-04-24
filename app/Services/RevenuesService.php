<?php

namespace App\Services;

use App\Models\Revenues;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class RevenuesService
{
    public function index($request): Collection|array {
        $date = isset($request['date'])
            ? Carbon::parse($request['date'])
            : Carbon::now();

        $query = Revenues::query()->whereMonth('receiving_date', $date->month)
            ->whereYear('receiving_date', $date->year)
            ->with('category');

        return $query->get();
    }

    public function show(string $id) {
        return Revenues::with('category')->find($id);
    }
}
