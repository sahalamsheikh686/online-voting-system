<?php

use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\HostRegistrationController;
use App\Http\Controllers\HostRequestController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PendingUserController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/register/invite/{token}', [AuthController::class, 'showRegisterFromInvite'])->name('register.invite');
    Route::get('/register/verify-otp', [AuthController::class, 'showRegisterOtp'])->name('register.otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyRegisterOtp'])->name('register.otp.store');
    Route::post('/register/resend-otp', [AuthController::class, 'resendRegisterOtp'])->name('register.otp.resend');
    Route::get('/hosts/create', [HostRegistrationController::class, 'create'])->name('hosts.create');
    Route::get('/hosts/verify-otp', [HostRegistrationController::class, 'showOtp'])->name('hosts.otp');
    Route::post('/hosts/verify-otp', [HostRegistrationController::class, 'verifyOtp'])->name('hosts.otp.store');
    Route::post('/hosts/resend-otp', [HostRegistrationController::class, 'resendOtp'])->name('hosts.otp.resend');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::post('/hosts', [HostRegistrationController::class, 'store'])->name('hosts.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('/host-requests', [HostRequestController::class, 'index'])->name('host-requests.index');
        Route::put('/host-requests/{user}', [HostRequestController::class, 'update'])->name('host-requests.update');
        Route::delete('/host-requests/{user}', [HostRequestController::class, 'destroy'])->name('host-requests.destroy');

        Route::get('/admins', [AdminManagementController::class, 'index'])->name('admins.index');
        Route::post('/admins', [AdminManagementController::class, 'store'])->name('admins.store');
        Route::put('/admins/{admin}', [AdminManagementController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [AdminManagementController::class, 'destroy'])->name('admins.destroy');
    });

    Route::middleware('manager')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/{election}/start-election', [DashboardController::class, 'startElection'])->name('dashboard.start-election');
        Route::post('/dashboard/{election}/pause-election', [DashboardController::class, 'pauseElection'])->name('dashboard.pause-election');
        Route::post('/dashboard/{election}/resume-election', [DashboardController::class, 'resumeElection'])->name('dashboard.resume-election');
        Route::delete('/dashboard/{election}', [DashboardController::class, 'destroyElection'])->name('dashboard.destroy-election');
        Route::post('/dashboard/archives/{archive}/restore', [DashboardController::class, 'restoreElection'])->name('dashboard.restore-election');
        Route::resource('candidates', CandidateController::class)->except(['show']);
        Route::get('/elections/create', [ElectionController::class, 'create'])->name('elections.create');
        Route::post('/elections', [ElectionController::class, 'store'])->name('elections.store');
        Route::delete('/elections/hard-delete', [ElectionController::class, 'hardDelete'])->name('elections.hard-delete');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        Route::get('/pending-users', [PendingUserController::class, 'index'])->name('pending-users.index');
        Route::put('/pending-users/{user}', [PendingUserController::class, 'update'])->name('pending-users.update');
        Route::delete('/pending-users/{user}', [PendingUserController::class, 'destroy'])->name('pending-users.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{type}/{format}', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('approved')->group(function () {
        Route::get('/vote', [VoteController::class, 'index'])->name('vote.index');
        Route::post('/vote', [VoteController::class, 'store'])->name('vote.store');
    });
});
