<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminDashboard\UserManagementController;
use App\Http\Controllers\Api\AdminDashboard\UserBanController;
use App\Http\Controllers\Api\AdminDashboard\RoleController;
use App\Http\Controllers\Api\AdminDashboard\PermissionController;
use App\Http\Controllers\Api\AdminDashboard\ProfileController;
use App\Http\Controllers\Api\AdminDashboard\CategoryController;
use App\Http\Controllers\Api\AdminDashboard\MediaController;
use App\Http\Controllers\Api\AdminDashboard\TagController;
use App\Http\Controllers\Api\AdminDashboard\ArticleController;

// Public
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Authenticated
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth & Profile ---
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // --- Common ---
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/tags/options', [TagController::class, 'options'])->name('tags.options');

    // --- User Management ---
    Route::get('/users/statuses', [UserManagementController::class, 'getStatuses'])
        ->middleware('permission:view-users')->name('users.statuses.index');

    Route::get('/users', [UserManagementController::class, 'index'])
        ->middleware('permission:view-users')->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])
        ->middleware('permission:create-users')->name('users.store');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])
        ->middleware('permission:view-users')->name('users.show');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])
        ->middleware('permission:edit-users')->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])
        ->middleware('permission:delete-users')->name('users.destroy');

    // --- User Governance ---
    Route::get('/users/{user}/ban-history', [UserBanController::class, 'index'])
        ->middleware('permission:govern-users')->name('users.ban-history');
    Route::post('/users/{user}/ban', [UserBanController::class, 'ban'])
        ->middleware('permission:govern-users')->name('users.ban');
    Route::post('/users/{user}/unban', [UserBanController::class, 'unban'])
        ->middleware('permission:govern-users')->name('users.unban');
    Route::post('/users/{user}/activate', [UserBanController::class, 'activate'])
        ->middleware('permission:govern-users')->name('users.activate');
    Route::post('/users/{user}/deactivate', [UserBanController::class, 'deactivate'])
        ->middleware('permission:govern-users')->name('users.deactivate');

    // --- Role Management ---
    Route::get('/roles/options', [RoleController::class, 'options'])
        ->middleware('permission:view-roles')->name('roles.options');
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:view-roles')->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:create-roles')->name('roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])
        ->middleware('permission:view-roles')->name('roles.show');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:edit-roles')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:delete-roles')->name('roles.destroy');

    // --- Category Management ---
    Route::get('/categories/groups', [CategoryController::class, 'getGroups'])
        ->middleware('permission:view-categories')->name('categories.groups');
    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('permission:view-categories')->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('permission:create-categories')->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])
        ->middleware('permission:view-categories')->name('categories.show');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:edit-categories')->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('permission:delete-categories')->name('categories.destroy');

    // --- Media Management ---
    Route::get('/media', [MediaController::class, 'index'])
        ->middleware('permission:view-media')->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])
        ->middleware('permission:create-media')->name('media.store');
    Route::get('/media/{media}', [MediaController::class, 'show'])
        ->middleware('permission:view-media')->name('media.show');
    Route::put('/media/{media}', [MediaController::class, 'update'])
        ->middleware('permission:edit-media')->name('media.update');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])
        ->middleware('permission:delete-media')->name('media.destroy');

    // --- Article Management ---
    Route::get('/articles', [ArticleController::class, 'index'])
        ->middleware('permission:view-articles')->name('articles.index');
    Route::post('/articles', [ArticleController::class, 'store'])
        ->middleware('permission:create-articles')->name('articles.store');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])
        ->middleware('permission:view-articles')->name('articles.show');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])
        ->middleware('permission:edit-articles')->name('articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])
        ->middleware('permission:delete-articles')->name('articles.destroy');
});
