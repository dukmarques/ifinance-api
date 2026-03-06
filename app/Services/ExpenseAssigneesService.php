<?php

namespace App\Services;

use App\Models\ExpenseAssignees;
use App\Http\Resources\ExpenseAssigneeResource;
use DomainException;

class ExpenseAssigneesService extends BaseService
{
    public function __construct()
    {
        $this->model = ExpenseAssignees::class;
        $this->resourceClass = ExpenseAssigneeResource::class;
    }

    public function destroy(string $id): bool
    {
        $resource = $this->model::query()->find($id);

        if (!$resource) {
            return false;
        }

        if ($resource->expense()->exists() || $resource->cardExpense()->exists()) {
            throw new DomainException(
                'Responsible cannot be removed because it is linked to expenses.'
            );
        }

        return $resource->delete();
    }
}
