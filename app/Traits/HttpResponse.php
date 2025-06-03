<?php

namespace App\Traits;

trait HttpResponse
{
    public function success(mixed $data, string $message = '', int $code = 200){
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function error(string $message, int $code = 500){
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }
}
