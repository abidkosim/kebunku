<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('superadmin')->group(function(){
    Route::get('/register', function(){
        return view('pages.superadmin-register');
    });

    Route::get('/login', fn() => view('pages.superadmin-login'))->name('superadmin.login');
    Route::get('/dashboard', fn() => view('pages.superadmin-dashboard'))->name('superadmin.dashboard');
});