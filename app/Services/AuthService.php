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
}
