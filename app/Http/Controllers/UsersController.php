<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    private UserService $service;

    public function __construct(UserService $service) {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->service->index();
        return response()->json($users, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = $this->service->show(Auth::id());

        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],
                'image' => 'filled'
                ],
            );

            $user = $this->service->store($request->all());

            return response()->json($user, 201);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'filled|min:3',
                'email' => 'filled|email'
            ]);

            $user = $this->service->update(Auth::id(), $request->all());
            return response()->json($user);
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
        try {
            $user = $this->service->destroy($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }
}
