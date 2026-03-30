<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\TriggerFlowController; // ✅ ADD THIS
use App\Http\Controllers\EnrollmentController;

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

        Route::post('/students/add-history', [StudentController::class, 'addHistory'])
    ->name('students.addHistory');
    
    Route::resource('students', StudentController::class);
    
    Route::post('/academic-periods', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\DB::table('academic_periods')->insert([
        'academic_year' => $request->academic_year,
        'term' => $request->term,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => true]);
    
});
    /*
    |--------------------------------------------------------------------------
    | Power Automate Trigger (Refresh Button)
    |--------------------------------------------------------------------------
    */

    Route::get('/trigger-flow', [TriggerFlowController::class, 'runFlow'])
        ->name('trigger.flow');   // ✅ ADD THIS

    
});

