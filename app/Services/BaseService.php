<?php

namespace App\Services;

use App\Http\Resources\ExpenseResource;
use App\Models\Expenses;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BaseService
{
    protected $model;

    protected $resourceClass;

    public function index()
    {
        $resources = $this->model::query()->get();
        return $this->resourceClass::collection($resources);
    }

    public function show(string $id): JsonResource|null
    {
        $resource = $this->model::query()->find($id);

        if (!$resource) {
            return null;
        }

        return new $this->resourceClass($resource);
    }

    public function store(array $data): JsonResource
    {
        $data['user_id'] = Auth::user()->id;
        $resource = $this->model::query()->create($data);
        return new $this->resourceClass($resource);
    }

    public function update(string $id, array $data): JsonResource|null
    {
        $resource = $this->model::query()->find($id);

        if (!$resource) {
            return null;
        }

        $resource->update($data);
        return new $this->resourceClass($resource);
    }

    public function destroy(string $id): bool
    {
        $resource = $this->model::query()->find($id);

        if (!$resource) {
            return false;
        }

        return $resource->delete();
    }
}
