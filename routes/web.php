<?php

use Inertia\Inertia;
use App\Models\UserGoogleAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\FirebaseUserController;
use App\Http\Controllers\FirebaseProjectController;
use App\Http\Controllers\UserGoogleAccountController;

Route::get('/', function () {
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    // ]);
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // google account routes
    Route::get('/auth/google', [UserGoogleAccountController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [UserGoogleAccountController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::delete('accounts/{id}', [UserGoogleAccountController::class, 'destroy'])->name('google.delete');

    Route::get('/dashboard', function () {
        $googleAccounts = UserGoogleAccount::where('user_id', Auth::id())->get();
        return Inertia::render('Dashboard', [
            'googleAccounts' => $googleAccounts,
        ]);
    })->name('dashboard');
    // Firebase Projects
    Route::get('/projects', [FirebaseProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [FirebaseProjectController::class, 'index'])->name('projects.get');


    //firebase project users
    Route::get('/users', [FirebaseUserController::class, 'index'])
        ->name('users.index');
    Route::post('/users', [FirebaseUserController::class, 'store'])
        ->name('users.store');
    Route::delete('/users/{uid}', [FirebaseUserController::class, 'destroy'])->name('users.destroy');
    Route::delete('/users/all/{project}', [FirebaseUserController::class, 'deleteAll'])->name('users.deleteAll');
    Route::post('/users/reset-password', [FirebaseUserController::class, 'resetPassword'])->name('users.resetPassword');
    // routes/web.php
    // Route::post('/users/reset-password-admin', [
    //     FirebaseUserController::class,
    //     'resetPasswordAdmin'
    // ])->name('users.resetPasswordAdmin');

    Route::post('/users/import', [FirebaseUserController::class, 'importUsers'])
        ->name('users.import');

    //Uploads
    Route::get('/uploads', [FirebaseProjectController::class, 'create'])->name('uploads.index');
    Route::post('/uploads', [FirebaseProjectController::class, 'store'])->name('uploads.store');
    Route::delete('/uploads/{firebaseProject}', [FirebaseProjectController::class, 'destroy'])->name('uploads.destroy');
});


require __DIR__ . '/auth.php';
