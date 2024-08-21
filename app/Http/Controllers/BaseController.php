<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    protected $service;

    protected $storeFormRequest;

    protected $updateFormRequest;

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
        app($this->storeFormRequest)->validateResolved();

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
    public function show(string $id)
    {
        $resource = $this->service->show($id);

        if(!$resource) {
            return response()->json([
                'message' => 'Resource not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($resource);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        app($this->updateFormRequest)->validateResolved();

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
            $delete = $this->service->destroy($id);

            if(!$delete) {
                return response()->json(['message' => 'Resource not found'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
