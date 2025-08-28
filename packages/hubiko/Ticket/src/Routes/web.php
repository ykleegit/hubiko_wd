<?php

use Illuminate\Support\Facades\Route;
use Hubiko\Ticket\Http\Controllers\TicketController;
use Hubiko\Ticket\Http\Controllers\TicketConversionController;
use Hubiko\Ticket\Http\Controllers\CategoryController;
use Hubiko\Ticket\Http\Controllers\PriorityController;
use Hubiko\Ticket\Http\Controllers\CustomFieldController;
use Hubiko\Ticket\Http\Controllers\SettingsController;

Route::group(['middleware' => ['auth', 'verified', 'PlanModuleCheck:Ticket']], function () {
    // Dashboard
    Route::get('ticket/dashboard', [TicketController::class, 'dashboard'])->name('ticket.dashboard');
    
    // Ticket management
    Route::resource('ticket', TicketController::class);
    Route::get('ticket-export', [TicketController::class, 'export'])->name('ticket.export');
    Route::delete('ticket/attachment/{ticket_id}/{id}', [TicketController::class, 'attachmentDestroy'])->name('ticket.attachment.destroy');
    Route::post('ticket/reply/{id}', [TicketController::class, 'reply'])->name('ticket.direct.reply');
    
    // Ticket chat/conversion
    Route::get('admin/chats', [TicketConversionController::class, 'index'])->name('admin.new.chat');
    Route::get('admin/chats/get-ticket', [TicketConversionController::class, 'getallTicket'])->name('admin.get.tickets');
    Route::get('admin/getticket-details/{id}', [TicketConversionController::class, 'getticketDetails'])->name('ticket.details');
    Route::post('admin/ticket/reply/{id}', [TicketConversionController::class, 'replystore'])->name('ticket.reply');
    Route::post('admin/ticket/note/{id}', [TicketConversionController::class, 'storeNote'])->name('ticket.note.store');
    Route::post('admin/ticket/status/change/{id}', [TicketConversionController::class, 'statusChange'])->name('ticket.status.change');
    Route::post('admin/ticket/agent/change/{id}', [TicketConversionController::class, 'assignChange'])->name('ticket.agent.change');
    Route::post('admin/ticket/category/change/{id}', [TicketConversionController::class, 'categoryChange'])->name('ticket.category.change');
    Route::post('admin/ticket/priority/change/{id}', [TicketConversionController::class, 'priorityChange'])->name('ticket.priority.change');
    Route::post('admin/ticket/name/change/{id}', [TicketConversionController::class, 'ticketnameChange'])->name('ticket.name.change');
    Route::post('admin/ticket/email/change/{id}', [TicketConversionController::class, 'ticketemailChange'])->name('ticket.email.change');
    Route::post('admin/ticket/subject/change/{id}', [TicketConversionController::class, 'ticketsubChange'])->name('ticket.subject.change');
    Route::get('admin/ticket/read-message/{id}', [TicketConversionController::class, 'readmessge'])->name('ticket.message.read');
    Route::get('admin/ticket/customfields/{id}', [TicketConversionController::class, 'ticketcustomfield'])->name('ticket.customfields');
    Route::post('admin/ticket/customfields/update/{id}', [TicketConversionController::class, 'ticketcustomfieldUpdate'])->name('ticket.customfields.update');
    
    // Categories
    Route::resource('ticket-category', CategoryController::class);
    
    // Priorities
    Route::resource('ticket-priority', PriorityController::class);
    
    // Custom Fields
    Route::resource('ticket-customfield', CustomFieldController::class);
    
    // Settings
    Route::post('ticket/settings/store', [SettingsController::class, 'store'])->name('ticket.settings.store');
}); 