<?php

use App\Contracts\SMS;
use App\Dtos\SendCodeData;
use App\Jobs\ProcessMail;
use App\Models\Mailing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! function_exists('calculatePercentageOf')) {
    function calculatePercentageOf($number, $total)
    {
        if ($number == 0 || $total == 0) {
            return 0;
        }

        $percentage = ($number / $total) * 100;

        return number_format($percentage, 2);
    }
}

if (! function_exists('userAuth')) {
    function userAuth()
    {
        return auth()->user();
    }
}

if (! function_exists('authUser')) {
    function authUser()
    {
        $user = request()->get('auth_user');

        return (object) $user;
    }
}

if (! function_exists('mailSend')) {
    function mailSend($type, $recipient, $subject, $mail_class, $payloadData = [])
    {
        $data = [
            'type' => $type,
            'email' => $recipient->email,
            'subject' => $subject,
            'body' => '',
            'mailable' => $mail_class,
            'scheduled_at' => now(),
            'payload' => array_merge($payloadData),
        ];

        $mailing = Mailing::saveData($data);
        dispatch(new ProcessMail($mailing->id));
    }
}

if (! function_exists('sendCode')) {
    function sendCode($request, SendCodeData $payload, ?string $method = null)
    {
        $channels = [
            'email' => function () use ($payload) {
                mailSend(
                    $payload->type,
                    $payload->user,
                    $payload->subject,
                    $payload->mailable,
                    $payload->data
                );
            },
            'sms' => function () use ($payload) {
                app(SMS::class)->sendSms(
                    $payload->phone,
                    $payload->message
                );
            },
        ];

        $getMethod = $request->method ?? $method;

        if (isset($channels[$getMethod])) {
            $channels[$getMethod]();
        } else {
            throw new \InvalidArgumentException("Unsupported method: {$getMethod}");
        }
    }
}

if (! function_exists('formatPhoneNumber')) {
    function formatPhoneNumber(string $phone_number): ?string
    {
        if (empty($phone_number)) {
            return null;
        }

        $phone_number = preg_replace('/\D/', '', $phone_number);

        if (preg_match('/^234[789][01]\d{8}$/', $phone_number)) {
            return $phone_number;
        }

        if (preg_match('/^0[789][01]\d{8}$/', $phone_number)) {
            return '234'.substr($phone_number, 1);
        }

        if (preg_match('/^\+234[789][01]\d{8}$/', $phone_number)) {
            return substr($phone_number, 1); // remove the '+' sign
        }

        return $phone_number;
    }
}

if (! function_exists('getCode')) {
    function getCode(int $length = 5): string
    {
        return str_pad(rand(0, 99999), $length, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('sendSmS')) {
    function sendSmS($phone, $message)
    {
        return app(SMS::class)->sendSms($phone, $message);
    }
}

if (! function_exists('generateUniqueNumber')) {
    function generateUniqueNumber($table, $column, $length = 10)
    {
        $attempts = 0;
        $maxAttempts = 10;

        do {
            if ($attempts++ > $maxAttempts) {
                throw new Exception("Unable to generate unique number after {$maxAttempts} attempts.");
            }

            $number = str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        } while (DB::connection('authuser')->table($table)->where($column, $number)->exists());

        return $number;
    }
}

if (! function_exists('sortColumn')) {
    function sortColumn($sort, $table)
    {
        if (! empty($sort)) {

            $column = explode(',', $sort)[0] ?? 'created_at';
            if (Schema::connection('transport')->hasColumn($table, $column)) {
                return $column;
            }
        }

        return 'created_at';
    }

}

if (! function_exists('sortDirection')) {
    function sortDirection($sort)
    {
        $direction = 'asc';
        if (! empty($sort)) {

            $direction = explode(',', $sort)[1] ?? 'asc';

            return in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        }

        return $direction;
    }
}
