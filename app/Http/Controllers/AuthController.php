<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private UserService $service;
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
}
