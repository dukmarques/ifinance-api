<?php

namespace App\Services;

use App\Models\ExpenseAssignees;
use App\Http\Resources\ExpenseAssigneeResource;

class ExpenseAssigneesService extends BaseService
{
    public function __construct()
    {
        $this->model = ExpenseAssignees::class;
        $this->resourceClass = ExpenseAssigneeResource::class;
    }
}
