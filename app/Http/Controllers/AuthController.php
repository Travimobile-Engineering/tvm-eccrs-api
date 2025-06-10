<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function userLogin(LoginRequest $request)
    {
        return $this->authService->userLogin($request);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        return $this->authService->forgotPassword($request);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->authService->resetPassword($request);
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    // Authentication for Micro APIs
    public function wipauthLogin(LoginRequest $request)
    {
        return $this->authService->authLogin($request);
    }

    public function wipforgotPassword(ForgotPasswordRequest $request)
    {
        return $this->authService->forgotPassword($request);
    }

    public function wipresetPassword(ResetPasswordRequest $request)
    {
        return $this->authService->resetPassword($request);
    }
}
