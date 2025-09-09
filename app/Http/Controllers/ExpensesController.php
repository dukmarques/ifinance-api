<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expenses;
use App\Services\ExpensesService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpensesController extends BaseController
{
    public function __construct(ExpensesService $service)
    {
        $this->service = $service;
        $this->storeFormRequest = CreateExpenseRequest::class;
        $this->updateFormRequest = UpdateExpenseRequest::class;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        try {
            $this->validate($request, [
                'delete_type' => 'required|in:' . implode(',', Expenses::$deleteTypes),
            ]);

            $delete = $this->service->destroy($id);

            if (!$delete) {
                return response()->json([
                    'message' => 'Expense not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->noContent();
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
