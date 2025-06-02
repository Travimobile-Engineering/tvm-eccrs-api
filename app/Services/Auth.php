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

        $url = config('services.auth_service.url') . $endpoint;

        return match (strtolower($method)) {
            'get'    => $client->get($url, $data),
            'post'   => $client->post($url, $data),
            'put'    => $client->put($url, $data),
            'patch'  => $client->patch($url, $data),
            'delete' => $client->delete($url, $data),
            default  => throw new \InvalidArgumentException("Unsupported method [$method]"),
        };
    }
}
