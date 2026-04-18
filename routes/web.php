<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


// Admin Dashboard Routes
Route::get('/admin/{any?}', function () {
    return view('app');
})->where('any', '.*');
