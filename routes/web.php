<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\TriggerFlowController; // ✅ ADD THIS

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Must Be Logged In)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('students', StudentController::class);

    Route::post('/students/import', [StudentController::class, 'import'])
        ->name('students.import');

    Route::resource('users', UserController::class);

    // Logout MUST be POST
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/account', function () {
        return view('account.settings');
    })->name('account.settings');

    Route::post('/account/update', [AuthController::class, 'updateProfile'])
        ->name('account.update');

    Route::post('/account/password', [AuthController::class, 'changePassword'])
        ->name('account.password');
        
    Route::post('/users/{id}/reset', [UserController::class, 'resetPassword'])
        ->name('users.reset');

    /*
    |--------------------------------------------------------------------------
    | Power Automate Trigger (Refresh Button)
    |--------------------------------------------------------------------------
    */

    Route::get('/trigger-flow', [TriggerFlowController::class, 'runFlow'])
        ->name('trigger.flow');   // ✅ ADD THIS
});