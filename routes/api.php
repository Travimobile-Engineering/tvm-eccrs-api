<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('eccrs')
    ->group(function () {
        Route::get('/health-check', fn () => response()->json([], 200));

        Route::middleware('validate.auth.header')
            ->prefix('auth')
            ->controller(AuthController::class)
            ->group(function () {
                Route::post('/login', 'userLogin');
                Route::post('/forgot-password', 'forgotPassword');
                Route::post('/reset-password', 'resetPassword');
            });

        Route::middleware(['auth:api', 'validate.header'])
            ->group(function () {
                Route::prefix('user')
                    ->controller(UserController::class)
                    ->group(function () {
                        Route::get('/travellers', 'getTravellers');
                        Route::get('/{user_id}/detail', 'getUserDetail');
                        Route::get('/agents', 'getAgents');
                        Route::get('/drivers', 'getDrivers');
                        Route::get('/stats', 'stats');
                        Route::get('/activities', 'getStateActivities');
                    });

                Route::prefix('transport')
                    ->controller(TransportController::class)
                    ->group(function () {
                        Route::get('/companies', 'getCompanies');
                        Route::get('/{id}/detail', 'getCompanyDetails');
                        Route::get('/{id}/drivers', 'getDrivers');
                        Route::get('/{id}/vehicles', 'getVehicles');
                        Route::get('/{id}/vehicle', 'getVehicle');
                        Route::get('/{id}/trips/{status?}', 'getTrips');
                    });

                Route::post('/auth/logout', [AuthController::class, 'logout']);
            });
    });
