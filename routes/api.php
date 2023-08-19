<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::group(['prefix' => 'auth', 'middleware' => 'cors'], function() {
Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);
});