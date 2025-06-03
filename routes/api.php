<?php

use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')
    ->controller(UserController::class)
    ->group(function(){
        
        Route::get('/travellers', 'getTravellers');
        Route::get('/{user_id}/get-detail', 'getUserDetail');
        Route::get('/agents', 'getAgents');
        Route::get('/drivers', 'getDrivers');
        Route::get('/stats', 'stats');
    });

Route::prefix('transport')
    ->controller(TransportController::class)
    ->group(function(){
        Route::get('/companies', 'getCompanies');
        Route::get('/{id}/get-detail', 'getCompanyDetails');
    });