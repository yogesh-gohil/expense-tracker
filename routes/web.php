<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('home')->middleware(['guest']);

Route::post('/logout', function () {
    Auth::guard('web')->logout();
});

Route::get('/auth/login', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('login')->middleware(['guest']);

Route::get('/auth/register', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('register')->middleware(['guest']);

Route::get('/login', function () {
    return redirect()->route('login');
});

Route::get('/register', function () {
    return redirect()->route('register');
});

Route::get('/{vue?}', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('main')->middleware(['redirect-if-unauthorized']);
