<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardExpenses\UpdateCardInstallmentsRequest;
use App\Services\CardInstallmentsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CardInstallmentsController extends Controller
{
    private CardInstallmentsService $service;

    public function __construct(CardInstallmentsService $service) {
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCardInstallmentsRequest $request, string $cardExpenseId, string $installmentId)
    {
        try {
            $update = $this->service->update(installmentId: $installmentId, updateData: $request->all());

            return response()->json($update, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
