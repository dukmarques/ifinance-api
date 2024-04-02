<?php

namespace App\Http\Controllers;

use App\Services\CardService;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    private CardService $service;
    public function __construct(CardService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cards = $this->service->index();
        return response()->json($cards);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|min:2',
                'closing_date' => 'filled|date',
                'due_date' => 'filled|date',
            ]);

            $card = $this->service->store($request->all());

            return response()->json($card, 201);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $card = $this->service->show($id);

        if(!$card) {
            return response()->json([
                'message' => 'Card not found'
            ], 404);
        }

        return response()->json($card);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $this->validate($request, [
                'name' => 'filled|min:2',
                'closing_date' => 'filled|date',
                'due_date' => 'filled|date',
            ]);

            $card = $this->service->update($id, $request->all());

            if(!$card) {
                return response()->json([
                    'message' => 'Card not found'
                ], 404);
            }

            return response()->json($card, 200);
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
        $card = $this->service->destroy($id);

        if(!$card) {
            return response()->json([
                'message' => 'Card not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Card deleted successfully'
        ]);
    }
}
