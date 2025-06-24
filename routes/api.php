<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

use App\Http\Controllers\Admin\AdminOrderController;

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Товары
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::put('/products/{product}', [AdminProductController::class, 'update']);
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);

    // Категории
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    // Если надо, позже можно добавить create/update/delete для категорий
});

Route::apiResource('/products', AdminProductController::class)->except(['show']);

Route::apiResource('/categories', AdminCategoryController::class)->except(['show']);

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('/categories', AdminCategoryController::class)->except(['show']);
});