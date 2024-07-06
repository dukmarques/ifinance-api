<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardExpenses\CreateCardExpensesRequest;
use App\Services\CardExpensesService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CardExpensesController extends Controller
{
    private CardExpensesService $service;

    public function __construct(CardExpensesService $service) {
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
    public function store(CreateCardExpensesRequest $request)
    {
        try {
            $create = $this->service->store($request->all());

            return response()->json($create, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            response()->json([
                'message' => $th->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
