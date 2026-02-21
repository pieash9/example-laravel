<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view("/home", "home");

Route::get("/user", [UserController::class, "getUser"]);
Route::get("/user/{name}", [UserController::class, "getUserName"]);