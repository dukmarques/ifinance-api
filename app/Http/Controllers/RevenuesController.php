<?php

namespace App\Http\Controllers;

use App\Models\Revenues;
use App\Services\RevenuesService;
use Illuminate\Http\Request;

class RevenuesController extends Controller
{
    private RevenuesService $service;

    public function __construct(RevenuesService $service) {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $revenues = $this->service->index($request->all());
        return response()->json($revenues);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $revenue = $this->service->show($id);

        if (!$revenue) {
            return response()->json([
                'message' => 'Revenue not found'
            ], 404);
        }

        return response()->json($revenue);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'bail|required|max:100|min:2',
                'amount' => 'bail|required|numeric|',
                'receiving_date' => 'bail|required|date|',
                'recurrent' => 'bail|required|boolean',
                'description' => 'filled|string|max:300',
                'category_id' => 'filled|uuid',
            ]);

            $revenue = $this->service->store($request->all());
            return response()->json($revenue, 201);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Revenues $revenues)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revenues $revenues)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revenues $revenues)
    {
        //
    }
}
