<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class Auth
{
    public function request(string $method, string $endpoint, array $data = [], ?string $token = null)
    {
        $client = Http::withHeaders([
            'X-App-Service' => config('services.auth_service.name'),
            config('security.auth_header_key') => config('security.auth_header_value'),
        ]);

        if ($token) {
            $client = $client->withToken($token);
        }

        return match (strtolower($method)) {
            'get'    => $client->get($endpoint, $data),
            'post'   => $client->post($endpoint, $data),
            'put'    => $client->put($endpoint, $data),
            'patch'  => $client->patch($endpoint, $data),
            'delete' => $client->delete($endpoint, $data),
            default  => throw new \InvalidArgumentException("Unsupported method [$method]"),
        };
    }
}
