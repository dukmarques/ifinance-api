<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function index(): Collection {
        return User::all();
    }

    public function store($data): User {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return User::create($data);
    }

    public function show($id): User|null {
        return User::find($id);
    }

    public function update($id, $data): User|null {
        $user = User::find($id);

        if(!$user) return null;

        $user->update($data);
        return $user;
    }

    public function destroy($id): bool {
        return User::destroy($id);
    }
}
