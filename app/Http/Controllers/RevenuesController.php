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
     * Show the form for creating a new resource.
     */
    public function create()
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
