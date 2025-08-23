<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RevenuesService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Revenues\CreateRevenuesRequest;
use App\Http\Requests\Revenues\UpdateRevenuesRequest;

class RevenuesController extends Controller
{
    public function __construct(private RevenuesService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $revenues = $this->service->index($request->all());
        return response()->json($revenues);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $revenue = $this->service->show($id);

        if (!$revenue) {
            return response()->json([
                'message' => 'Revenue not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($revenue);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRevenuesRequest $request)
    {
        try {
            $revenue = $this->service->store($request->validated());
            return response()->json($revenue, 201);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRevenuesRequest $request, string $id)
    {
        try {
            $revenue = $this->service->update($id, $request->validated());

            if (!$revenue) {
                return response()->json([
                    'message' => 'Revenue not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($revenue);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $revenue = $this->service->destroy($id);

        if (!$revenue) {
            return response()->json([
                'message' => 'Revenue not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Revenue deleted successfully'
        ], Response::HTTP_NO_CONTENT);
    }
}
