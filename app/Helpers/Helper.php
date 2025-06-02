<?php

if (function_exists('authUser')) {
    function authUser() {
        $user = request()->get('auth_user');
        return (object) $user;
    }
}



