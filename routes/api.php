<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminDashboard\UserManagementController;
use App\Http\Controllers\Api\AdminDashboard\UserBanController;
use App\Http\Controllers\Api\AdminDashboard\RoleController;
use App\Http\Controllers\Api\AdminDashboard\PermissionController;
use App\Http\Controllers\Api\AdminDashboard\ProfileController;
use App\Http\Controllers\Api\AdminDashboard\CategoryController;
use App\Http\Controllers\Api\AdminDashboard\MediaController;
use App\Http\Controllers\Api\AdminDashboard\TagController;
use App\Http\Controllers\Api\AuthController;

// Public Auth Routes
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // User Management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/statuses', [UserManagementController::class, 'getStatuses'])->name('users.statuses.index');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Advanced Account Governance (Banning)
    Route::get('/users/{user}/ban-history', [UserBanController::class, 'index'])->name('users.ban-history');
    Route::post('/users/{user}/ban', [UserBanController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [UserBanController::class, 'unban'])->name('users.unban');
    Route::post('users/{user}/activate', [UserBanController::class, 'activate'])->name('users.activate');
    Route::post('users/{user}/deactivate', [UserBanController::class, 'deactivate'])->name('users.deactivate');

    // Roles & Permissions
    Route::get('/roles/options', [RoleController::class, 'options'])->name('roles.options');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // Category Management
    Route::get('/categories/groups', [CategoryController::class, 'getGroups'])->name('categories.groups');
    Route::apiResource('categories', CategoryController::class);

    // Tag Options
    Route::get('/tags/options', [TagController::class, 'options'])->name('tags.options');

    // Media Management
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');
    Route::put('/media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
});
