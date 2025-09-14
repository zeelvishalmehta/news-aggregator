<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/articles', [ArticleController::class, 'index']);
Route::middleware('auth:sanctum')->get('/articles/{id}', [ArticleController::class, 'show']);

Route::post('/articles/fetch', function () {
    Artisan::call('app:fetch-articles');
    return response()->json([
        'status' => 'success',
        'message' => 'Articles fetched successfully',
    ]);
})->middleware('auth:sanctum');