<?php

if (! function_exists('calculatePercentageDifference')) {
    function calculatePercentageDifference($value1, $value2)
    {
        if ($value1 == 0) {
            return $value2 > 0 ? 100 : 0;
        }

        $diff = (($value2 - $value1) / $value1) * 100;

        return $diff > 0 ? $diff : 0;
    }
}

if (function_exists('authUser')) {
    function authUser()
    {
        $user = request()->get('auth_user');

        return (object) $user;
    }
}
