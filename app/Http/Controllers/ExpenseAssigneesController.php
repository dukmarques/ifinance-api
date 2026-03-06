<?php

namespace App\Http\Controllers;

use App\Services\ExpenseAssigneesService;
use App\Http\Requests\ExpenseAssignees\CreateExpenseAssigneeRequest;
use App\Http\Requests\ExpenseAssignees\UpdateExpenseAssigneeRequest;
use DomainException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpenseAssigneesController extends BaseController
{
    public function __construct(ExpenseAssigneesService $service)
    {
        $this->service = $service;
        $this->storeFormRequest = CreateExpenseAssigneeRequest::class;
        $this->updateFormRequest = UpdateExpenseAssigneeRequest::class;
    }

    public function destroy(string $id, Request $request)
    {
        try {
            $delete = $this->service->destroy($id);

            if (!$delete) {
                return $this->responseWithResrouceNotFound();
            }

            return $this->responseWithNoContent();
        } catch (DomainException $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
