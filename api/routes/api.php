<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HotelAvailabilityController;
use App\Http\Controllers\Api\V1\HotelController;
use App\Http\Controllers\Api\V1\HotelRevenueReportController;
use App\Http\Controllers\Api\V1\HotelUserController;
use App\Http\Controllers\Api\V1\ImportIssueController;
use App\Http\Controllers\Api\V1\ImportRunController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\ReservationPaymentController;
use App\Http\Controllers\Api\V1\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('v1.')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::get('hotels', [HotelController::class, 'index'])->name('hotels.index');
    Route::get('hotels/{hotel}/availability', [HotelAvailabilityController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('hotels.availability');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::delete('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

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
        Route::get('reservations/{reservation}/payments', [ReservationPaymentController::class, 'index'])
            ->name('reservations.payments.index');

        Route::get('hotels/{hotel}/revenue-reports', [HotelRevenueReportController::class, 'show'])
            ->name('hotels.revenue-reports.show');

        Route::get('hotels/{hotel}/users', [HotelUserController::class, 'index'])->name('hotels.users.index');
        Route::post('hotels/{hotel}/users', [HotelUserController::class, 'store'])->name('hotels.users.store');
        Route::patch('hotels/{hotel}/users/{user}', [HotelUserController::class, 'update'])->name('hotels.users.update');
        Route::delete('hotels/{hotel}/users/{user}', [HotelUserController::class, 'destroy'])->name('hotels.users.destroy');
    });
});
