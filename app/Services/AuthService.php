<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService {
    public function login($data): string {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'message' => 'The provided credentials are incorrect'
            ]);
        }

        $user->tokens()->delete();

        return $user->createToken($data['device_name'])->plainTextToken;
    }

    public function logout(Request $request): bool {
        return $request->user()->tokens()->delete();
    }
}
