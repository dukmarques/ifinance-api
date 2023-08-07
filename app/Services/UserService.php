<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function index(): Collection {
        return User::all();
    }

    public function store($data): User {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return User::create($data);
    }

    public function show(): User|null {
        return Auth::user();
    }

    public function update($data): User|null {
        $user = Auth::user();

        $user->update($data);
        return $user;
    }

    public function destroy($id): bool {
        return User::destroy($id);
    }
}
