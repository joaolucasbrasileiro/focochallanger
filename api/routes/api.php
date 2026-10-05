<?php

use App\Http\Controllers\Api\V1\HotelAvailabilityController;
use App\Http\Controllers\Api\V1\HotelController;
use App\Http\Controllers\Api\V1\ImportIssueController;
use App\Http\Controllers\Api\V1\ImportRunController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('v1.')->group(function (): void {
    Route::get('hotels', [HotelController::class, 'index'])->name('hotels.index');
    Route::get('hotels/{hotel}/availability', [HotelAvailabilityController::class, 'show'])->name('hotels.availability');

    Route::get('import-runs', [ImportRunController::class, 'index'])->name('import-runs.index');
    Route::get('import-runs/{importRun}', [ImportRunController::class, 'show'])->name('import-runs.show');
    Route::get('import-issues', [ImportIssueController::class, 'index'])->name('import-issues.index');
    Route::get('import-issues/{importIssue}', [ImportIssueController::class, 'show'])->name('import-issues.show');

    Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::put('rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::patch('rooms/{room}', [RoomController::class, 'update'])->name('rooms.update-partial');
    Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
});
