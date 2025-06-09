<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Traits\HttpResponse;
use App\Traits\LoginTrait;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    use HttpResponse, LoginTrait;

    public function __construct(
        protected Auth $auth
    ) {}

    public function userLogin($request)
    {
        try {
            $user = AuthUser::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return $this->error(null, 'Credentials do not match', 401);
            }

            if ($res = $this->authCheck($user)) {
                return $res;
            }

            $token = JWTAuth::fromUser($user);

            $response = array_merge([
                'token' => $token,
                'user' => $user,
            ], $this->additionalData($user));

            return $this->success($response, 'Login successful');

        } catch (JWTException $e) {
            return $this->error(null, 'An error occurred: '.$e->getMessage(), 500);
        }
    }

    public function authLogin($request)
    {
        $url = config('services.auth_service.url').'/auth/login';

        $response = $this->auth->request('post', $url, [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        return $response->json();
    }

    public function forgotPassword($request)
    {
        $url = config('services.auth_service.url').'/auth/forgot-password';

        $response = $this->auth->request('post', $url, [
            'email' => $request->input('email'),
        ]);

        return $response->json();
    }

    public function resetPassword($request)
    {
        $url = config('services.auth_service.url').'/auth/reset-password';

        $response = $this->auth->request('post', $url, [
            'code' => $request->input('code'),
            'new_password' => $request->input('new_password'),
            'new_password_confirmation' => $request->input('new_password_confirmation'),
        ]);

        return $response->json();
    }
}
