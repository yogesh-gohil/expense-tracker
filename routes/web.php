<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('home')->middleware(['guest']);

Route::post('/logout', function () {
    Auth::guard('web')->logout();
});

Route::get('/login', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('login')->middleware(['guest']);

Route::get('/{vue?}', function () {
    return view('app');
})->where('vue', '[\/\w\.-]*')->name('main')->middleware(['redirect-if-unauthorized']);
