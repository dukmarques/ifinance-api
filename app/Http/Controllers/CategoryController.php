<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->service->index();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = $this->service->show($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json($category);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|min:2'
            ]);

            $category = $this->service->store($request->all());
            return response()->json($category, 201);
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
                'name' => 'filled|min:2'
            ]);

            $category = $this->service->update($id, $request->all());

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json($category);
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
        $category = $this->service->destroy($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}
