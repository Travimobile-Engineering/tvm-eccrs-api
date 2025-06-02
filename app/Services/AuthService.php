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
        $response = $this->auth->request('post', '/auth/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        return $response->json();
    }
}
