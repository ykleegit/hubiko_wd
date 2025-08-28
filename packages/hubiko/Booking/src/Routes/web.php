<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Hubiko\Booking\Http\Controllers\BookingController;
use Hubiko\Booking\Http\Controllers\GuestController;
use Hubiko\Booking\Http\Controllers\RoomController;

Route::group(['middleware' => 'PlanModuleCheck:Booking'], function () {
    Route::prefix('booking')->group(function () {
        
        // Booking Management Routes
        Route::resource('bookings', BookingController::class);
        Route::get('bookings/{id}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
        Route::post('bookings/{id}/check-in', [BookingController::class, 'processCheckIn'])->name('bookings.process-check-in');
        Route::get('bookings/{id}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
        Route::post('bookings/{id}/check-out', [BookingController::class, 'processCheckOut'])->name('bookings.process-check-out');
        
        // Guest Management Routes
        Route::resource('guests', GuestController::class);
        Route::get('guests/{id}/bookings', [GuestController::class, 'bookings'])->name('guests.bookings');
        
        // Room Management Routes
        Route::resource('rooms', RoomController::class);
        Route::get('rooms/{id}/availability', [RoomController::class, 'availability'])->name('rooms.availability');
        Route::post('rooms/{id}/set-availability', [RoomController::class, 'setAvailability'])->name('rooms.set-availability');
        
        // Calendar and Dashboard Routes
        Route::get('calendar', [BookingController::class, 'calendar'])->name('booking.calendar');
        Route::get('dashboard', [BookingController::class, 'dashboard'])->name('booking.dashboard');
        Route::get('reports', [BookingController::class, 'reports'])->name('booking.reports');
        
    });
});
