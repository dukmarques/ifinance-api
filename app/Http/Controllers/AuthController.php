<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct(AuthService $service) {
        $this->service = $service;
    }

    public function login(Request $request) {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required',
                'device_name' => 'required'
            ]);

            $token = $this->service->login($request->all());

            return response()->json([
                'token' => $token
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], 400);
        }
    }

    public function logout(Request $request) {
        $logout = $this->service->logout($request);

        if($logout) {
            return response()->json();
        }

        return response()->json([
            'message' => 'An error occurred while logging out user'
        ], 500);
    }
}
