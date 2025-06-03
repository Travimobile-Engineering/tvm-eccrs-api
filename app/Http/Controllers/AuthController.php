<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest as RequestsLoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\LoginRequest;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    )
    {}

    public function authLogin(RequestsLoginRequest $request)
    {
        return $this->authService->authLogin($request);
    }
}
