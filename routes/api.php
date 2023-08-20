<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecipesController;


Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);

Route::group(["middleware" => "auth:api"], function () {
    Route::get('/getRecipe/{recipeId?}', [RecipesController::class, 'getRecipe']);
    Route::post('/getRecipe/{recipeId}/like', [RecipesController::class, 'likeRecipe']);
    Route::post('/getRecipe/{recipeId}/comment', [RecipesController::class, 'addComment']);
});