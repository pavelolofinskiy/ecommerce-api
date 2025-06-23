<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

use App\Http\Controllers\Api\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/add', [CartController::class, 'addItem']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeItem']);

    Route::post('/checkout', [OrderController::class, 'checkout']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // сюда маршруты корзины и заказов из прошлого шага
});

use App\Http\Controllers\Api\AddressController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('addresses', AddressController::class);
});

use App\Http\Controllers\Api\OrderController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});