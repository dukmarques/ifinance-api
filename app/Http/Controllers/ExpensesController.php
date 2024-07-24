<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expenses;
use App\Services\ExpensesService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpensesController extends Controller
{
    private ExpensesService $service;
    public function __construct(ExpensesService $service) {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateExpenseRequest $request)
    {
        try {
            $create = $this->service->store($request->all());

            return response()->json($create, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expenses $expenses)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, string $id)
    {
        try {
            $update = $this->service->update($id, $request->all());

            if (!$update) {
                return response()->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
            }

            return response()->json($update);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
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

            $delete = $this->service->destroy($id, $request->input('delete_type'));

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
