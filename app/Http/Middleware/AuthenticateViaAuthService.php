<?php

namespace App\Http\Middleware;

use App\Services\Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Trait\HttpResponses;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuthenticateViaAuthService
{
    use HttpResponses;

    public function __construct(
        protected Auth $auth
    )
    {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $request->hasHeader('X-App-Service')) {
            return $this->error(null, 'Service name not configured', 500);
        }

        if (!$token) {
            return $this->error(null, 'Token missing', 401);
        }

        $cacheKey = 'auth_token_valid:' . sha1($token);

        $data = Cache::get($cacheKey);

        if (!$data) {
            $response = $response = $this->auth->request('get', '/auth/validate', [], $token);
            $data = $response->json();

            if ($response->successful() && ($data['data']['valid'] ?? false)) {
                Cache::put($cacheKey, $data, now()->addMinutes(2));
            }
        }

        if (!($data['status'] ?? false)) {
            return $this->error(null, "Unauthenticated! {$data['message']}", 401);
        }

        if (($data['data']['valid'] ?? false)) {
            $request->merge(['auth_user' => $data['data']['user'] ?? null]);
            return $next($request);
        }

        return $this->error(null, "Unauthorized! {$data['message']}", 401);
    }
}
