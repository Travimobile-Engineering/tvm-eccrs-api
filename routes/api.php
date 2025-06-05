<?php

use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')
->controller(AuthController::class)
    ->group(function () {
        Route::post('/login', 'authLogin');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'resetPassword');
});

Route::prefix('user')
    ->controller(UserController::class)
    ->group(function(){
        
        Route::get('/travellers', 'getTravellers');
        Route::get('/{user_id}/detail', 'getUserDetail');
        Route::get('/agents', 'getAgents');
        Route::get('/drivers', 'getDrivers');
        Route::get('/stats', 'stats');
        Route::get('/activities', 'getStateActivities');
    });

Route::prefix('transport')
    ->controller(TransportController::class)
    ->group(function(){
        Route::get('/companies', 'getCompanies');
        Route::get('/{id}/detail', 'getCompanyDetails');
        Route::get('/{id}/drivers', 'getDrivers');
        Route::get('/{id}/vehicles', 'getVehicles');
        Route::get('/{id}/vehicle', 'getVehicle');
        Route::get('/{id}/trips/{status?}', 'getTrips');
    });