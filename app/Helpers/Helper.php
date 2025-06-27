<?php

use App\Contracts\SMS;
use App\Dtos\SendCodeData;
use App\Jobs\ProcessMail;
use App\Models\Mailing;

if (! function_exists('calculatePercentageDifference')) {
    function calculatePercentageDifference($value1, $value2)
    {
        if ($value1 == 0) {
            return $value2 > 0 ? 100 : 0;
        }

        $diff = (($value2 - $value1) / $value1) * 100;

        return number_format($diff, 2);
    }
}

if (function_exists('authUser')) {
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
