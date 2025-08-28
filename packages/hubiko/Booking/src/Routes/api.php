<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Hubiko\Booking\Http\Controllers\Api\BookingApiController;
use Hubiko\Booking\Http\Controllers\Api\GuestApiController;
use Hubiko\Booking\Http\Controllers\Api\RoomApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'PlanModuleCheck:Booking'], function () {
    Route::prefix('booking')->group(function () {
        
        // Booking API Routes
        Route::apiResource('bookings', BookingApiController::class);
        Route::get('bookings/{id}/status', [BookingApiController::class, 'getStatus']);
        Route::post('bookings/{id}/update-status', [BookingApiController::class, 'updateStatus']);
        
        // Guest API Routes
        Route::apiResource('guests', GuestApiController::class);
        Route::get('guests/search/{query}', [GuestApiController::class, 'search']);
        
        // Room API Routes
        Route::apiResource('rooms', RoomApiController::class);
        Route::get('rooms/available/{date}', [RoomApiController::class, 'getAvailableRooms']);
        Route::get('rooms/{id}/bookings/{date}', [RoomApiController::class, 'getRoomBookings']);
        
    });
});
