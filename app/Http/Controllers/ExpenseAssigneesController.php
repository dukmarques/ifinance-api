<?php

namespace App\Http\Controllers;

use App\Services\ExpenseAssigneesService;
use App\Http\Requests\ExpenseAssignees\CreateExpenseAssigneeRequest;
use App\Http\Requests\ExpenseAssignees\UpdateExpenseAssigneeRequest;

class ExpenseAssigneesController extends BaseController
{
    public function __construct(ExpenseAssigneesService $service)
    {
        $this->service = $service;
        $this->storeFormRequest = CreateExpenseAssigneeRequest::class;
        $this->updateFormRequest = UpdateExpenseAssigneeRequest::class;
    }
}
