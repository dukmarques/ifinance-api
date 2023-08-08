<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    private TransactionService $service;
    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = $this->service->index();
        return response()->json($transactions);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = $this->service->show(id: $id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json($transaction);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required|min:2',
                'price' => 'required|numeric',
                'type' => 'required|in:entry,exit',
                'is_owner' => 'required|boolean',
                'date' => 'required|date',
                'pay_month' => 'required|date',
                'paid_out' => 'required|boolean',
                'card_id' => 'filled',
                'category_id' => 'filled'
            ]);

            $transaction = $this->service->store(data: $request->all());
            return response()->json($transaction, 201);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $this->validate($request, [
                'title' => 'filled|min:2',
                'price' => 'filled|numeric',
                'type' => 'filled|in:entry,exit',
                'is_owner' => 'filled|boolean',
                'date' => 'filled|date',
                'pay_month' => 'filled|date',
                'paid_out' => 'filled|boolean',
                'card_id' => 'filled',
                'category_id' => 'filled'
            ]);

            $transaction = $this->service->update(id: $id, data: $request->all());

            if (!$transaction) {
                return response()->json([
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json($transaction);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = $this->service->destroy(id: $id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Transaction deleted successfully'
        ]);
    }
}
