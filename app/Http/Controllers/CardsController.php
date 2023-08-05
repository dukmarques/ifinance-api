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
    public function index($user_id)
    {
        $cards = $this->service->index($user_id);

        if(!$cards) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json($cards);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $user_id)
    {
        try {
            $this->validate($request, [
                'name' => 'required|min:2',
                'closing_date' => 'filled|date',
                'due_date' => 'filled|date',
            ]);

            $card = $this->service->store($user_id, $request->all());

            if(!$card) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

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
    public function show(string $user_id, string $card_id)
    {
        $card = $this->service->show($user_id, $card_id);

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
    public function update(Request $request, string $user_id, string $card_id)
    {
        try {
            $this->validate($request, [
                'name' => 'filled|min:2',
                'closing_date' => 'filled|date',
                'due_date' => 'filled|date',
            ]);

            $card = $this->service->update($user_id, $card_id, $request->all());

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
    public function destroy(string $user_id, string $card_id)
    {
        $card = $this->service->destroy($user_id, $card_id);

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
