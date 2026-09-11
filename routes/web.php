<?php

use App\n2_System\Access\Http\Controllers\RoleController;
use App\n2_System\Http\Controllers\DashboardController;
use App\n2_System\Identity\Http\Controllers\UserController;
use App\n2_System\Widgets\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('system/widgets', WidgetController::class)->name('system.widgets');
    Route::resource('system/users', UserController::class)->except('show')->names('system.users');
    Route::get('system/roles', [RoleController::class, 'edit'])->name('system.roles.edit');
    Route::put('system/roles', [RoleController::class, 'update'])->name('system.roles.update');
});

require __DIR__.'/settings.php';
