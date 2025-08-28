<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Hubiko\Ticket\Http\Controllers\Api\TicketApiController;
use Hubiko\Ticket\Http\Controllers\Api\CategoryApiController;
use Hubiko\Ticket\Http\Controllers\Api\PriorityApiController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Tickets API
    Route::get('tickets', [TicketApiController::class, 'index'])->name('api.tickets.index');
    Route::post('tickets', [TicketApiController::class, 'store'])->name('api.tickets.store');
    Route::get('tickets/{id}', [TicketApiController::class, 'show'])->name('api.tickets.show');
    Route::put('tickets/{id}', [TicketApiController::class, 'update'])->name('api.tickets.update');
    Route::delete('tickets/{id}', [TicketApiController::class, 'destroy'])->name('api.tickets.destroy');
    Route::post('tickets/{id}/reply', [TicketApiController::class, 'reply'])->name('api.tickets.reply');
    Route::put('tickets/{id}/status', [TicketApiController::class, 'updateStatus'])->name('api.tickets.status.update');
    Route::put('tickets/{id}/assign', [TicketApiController::class, 'assignTicket'])->name('api.tickets.assign');
    
    // Categories API
    Route::get('categories', [CategoryApiController::class, 'index'])->name('api.categories.index');
    Route::post('categories', [CategoryApiController::class, 'store'])->name('api.categories.store');
    Route::get('categories/{id}', [CategoryApiController::class, 'show'])->name('api.categories.show');
    Route::put('categories/{id}', [CategoryApiController::class, 'update'])->name('api.categories.update');
    Route::delete('categories/{id}', [CategoryApiController::class, 'destroy'])->name('api.categories.destroy');
    
    // Priorities API
    Route::get('priorities', [PriorityApiController::class, 'index'])->name('api.priorities.index');
    Route::post('priorities', [PriorityApiController::class, 'store'])->name('api.priorities.store');
    Route::get('priorities/{id}', [PriorityApiController::class, 'show'])->name('api.priorities.show');
    Route::put('priorities/{id}', [PriorityApiController::class, 'update'])->name('api.priorities.update');
    Route::delete('priorities/{id}', [PriorityApiController::class, 'destroy'])->name('api.priorities.destroy');
});

// Public API for creating tickets (no authentication required)
Route::post('public/tickets', [TicketApiController::class, 'createPublicTicket'])->name('api.public.tickets.create');
Route::get('public/tickets/{ticket_id}/status', [TicketApiController::class, 'getPublicTicketStatus'])->name('api.public.tickets.status'); 