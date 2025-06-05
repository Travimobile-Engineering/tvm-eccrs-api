<?php

namespace App\Services;

class AuthService
{
    public function __construct(
        protected Auth $auth
    )
    {}

    public function authLogin($request)
    {
        $url = config('services.auth_service.url') . '/auth/login';

        $response = $this->auth->request('post', $url, [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        return $response->json();
    }

    public function forgotPassword($request)
    {
        $url = config('services.auth_service.url') . '/auth/forgot-password';

        $response = $this->auth->request('post', $url, [
            'email' => $request->input('email')
        ]);

        return $response->json();
    }

    public function resetPassword($request)
    {
        $url = config('services.auth_service.url') . '/auth/reset-password';

        $response = $this->auth->request('post', $url, [
            'code' => $request->input('code'),
            'new_password' => $request->input('new_password'),
            'new_password_confirmation' => $request->input('new_password_confirmation')
        ]);

        return $response->json();
    }
}
