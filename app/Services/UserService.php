<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use phpDocumentor\Reflection\Types\Null_;

class UserService extends BaseService
{
    public function __construct()
    {
        $this->model = User::class;
        $this->resourceClass = UserResource::class;
    }

    public function store($data): UserResource
    {
        $data['password'] = Hash::make($data['password']);
        return new UserResource(User::query()->create($data));
    }
}
