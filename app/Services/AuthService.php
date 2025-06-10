<?php

namespace App\Services;

use App\Dtos\SendCodeData;
use App\Enums\MailingEnum;
use App\Mail\ForgotPasswordMail;
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

    public function forgotPassword($request)
    {
        $user = AuthUser::where('email', $request->email)->first();

        if (! $user) {
            return $this->error(null, "We can\'t find a user with that email address", 404);
        }

        $code = getCode();

        $user->update([
            'reset_code' => $code,
            'reset_code_expires_at' => now()->addMinutes(15),
        ]);

        $data = [
            'name' => $user->first_name,
            'code' => $code,
        ];

        sendCode(
            $request,
            new SendCodeData(
                type: MailingEnum::RESET_CODE,
                user: $user,
                data: $data,
                phone: formatPhoneNumber($user->phone_number),
                message: "Your Travi Reset Pin is: $code. Valid for 10 mins. Do not share with anyone. Powered By Travi",
                subject: 'Your Password Reset Code',
                mailable: ForgotPasswordMail::class,
            ),
            'email'
        );

        return $this->success(null, 'Verification code sent successfully');
    }

    public function resetPassword($request)
    {
        $user = AuthUser::where('email', $request->email)
            ->where('reset_code', $request->code)
            ->where('reset_code_expires_at', '>=', now())
            ->first();

        if (! $user) {
            return $this->error(null, 'Invalid or expired code.', 400);
        }

        $user->update([
            'reset_code' => null,
            'reset_code_expires_at' => null,
            'password' => bcrypt($request->new_password),
        ]);

        return $this->success(null, 'Password reset successfully');
    }

    public function logout()
    {
        try {
            $token = auth('api')->getToken();

            if (! $token) {
                return $this->error(null, 'Token missing', 401);
            }

            auth('api')->invalidate($token);

            return $this->success(null, 'Logged out successfully');
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->error(null, 'Failed to log out, token invalid or expired', 500);
        }
    }

    // Authentication for Micro APIs
    public function authLogin($request)
    {
        $url = config('services.auth_service.url').'/auth/login';

        $response = $this->auth->request('post', $url, [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        return $response->json();
    }

    public function wipforgotPassword($request)
    {
        $url = config('services.auth_service.url').'/auth/forgot-password';

        $response = $this->auth->request('post', $url, [
            'email' => $request->input('email'),
        ]);

        return $response->json();
    }

    public function wipresetPassword($request)
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
