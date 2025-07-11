<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;

Route::middleware('validate.header')
    ->prefix('eccrs')
    ->group(function () {
        Route::get('/health-check', fn () => response()->json([], 200))
            ->withoutMiddleware('validate.header');

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
                // User
                Route::prefix('user')
                    ->controller(UserController::class)
                    ->group(function () {
                        Route::get('/travellers', 'getTravellers');
                        Route::get('/{user_id}/detail', 'getUserDetail');
                        Route::get('/agents', 'getAgents');
                        Route::get('/drivers', 'getDrivers');
                        Route::get('/stats', 'stats');
                        Route::get('/stats/activities', 'statActivities');
                        Route::get('/activities', 'getStateActivities');
                    });

                // Transport
                Route::prefix('transport')
                    ->controller(TransportController::class)
                    ->group(function () {
                        Route::get('/companies', 'getCompanies');
                        Route::get('/{id}/detail', 'getCompanyDetails');
                        Route::get('/{id}/drivers', 'getDrivers');
                        Route::get('/vehicle/{id}/detail', 'getVehicle');
                        Route::get('/{id}/vehicles', 'getVehicles');
                        Route::get('/{id}/trips/{status?}', 'getTrips');
                        Route::get('/stats', 'getStats');
                        Route::get('/zone/{zone?}', 'getZoneData');
                    });

                // Manifest
                Route::prefix('manifest')
                    ->controller(ManifestController::class)
                    ->group(function () {
                        Route::get('/', 'getManifests');
                        Route::get('/{id}/detail', 'getManifestDetail');
                    });

                // Wathclist
                Route::prefix('watchlist')
                    ->controller(WatchlistController::class)
                    ->group(function () {
                        Route::get('/all', 'getWatchlistRecords');
                        Route::get('/{id}/detail', 'getWatchlistDetail');
                        Route::get('/stats', 'watchlistStats');
                    });
                
                // Incident
                Route::prefix('incident')
                    ->controller(IncidentController::class)
                    ->group(function () {
                        Route::get('/all', 'getIncidents');
                        Route::get('/stats', 'getIncidentStats');
                    });

                // Report
                Route::prefix('reports')
                    ->controller(ReportController::class)
                    ->group(function () {
                        Route::get('/', 'getReports');
                        Route::post('/export', 'exportReports');
                        Route::get('/manifest/{id}/detail', 'getReportDetail');
                    });

                // Settings
                Route::prefix('settings')
                    ->controller(SettingsController::class)
                    ->group(function () {
                        // Account
                        Route::prefix('account')
                            ->group(function () {
                                Route::post('/create', 'createAccount');
                                Route::post('/suspend', 'suspendAccount');
                                Route::post('/activate', 'activateAccount');
                                Route::post('/change-password', 'changePasword');
                                Route::get('/', 'getAccounts');
                                Route::get('/{id}', 'getAccount');
                                Route::put('/{id}/update', 'updateAccount');
                                Route::delete('/{id}/delete', 'deleteAccount');
                            });

                        // Organization
                        Route::prefix('organization')
                            ->group(function () {
                                Route::post('/create', 'createOrganization');
                                Route::get('/', 'getOrganizations');
                                Route::get('/{id}', 'getOrganization');
                                Route::put('/{id}/update', 'updateOrganization');
                                Route::delete('/{id}/delete', 'deleteOrganization');
                            });

                        // Roles
                        Route::prefix('role')
                            ->group(function () {
                                Route::post('/create', 'createRole');
                                Route::get('/', 'getRoles');
                                Route::get('/{id}', 'getRole');
                                Route::put('/{id}/update', 'updateRole');
                                Route::delete('/{id}/delete', 'deleteRole');
                            });

                        // Permissions
                        Route::prefix('permission')
                            ->group(function () {
                                Route::get('/', 'getPermissions');
                            });

                        // Profile
                        Route::get('/profile/{user_id}', 'getProfile');
                        Route::post('/profile/change-phone-number', 'changePhoneNumber');
                        Route::post('/profile/validate-phone-number', 'validatePhoneNumber');

                        // System Log
                        Route::get('/system-log', 'getSystemLog');
                    });

                // Other APIs
                Route::get('/states', [GeneralController::class, 'getStates']);
                Route::get('/zones', [GeneralController::class, 'getZones']);

                // Auth
                Route::post('/auth/logout', [AuthController::class, 'logout']);
            });
    });
