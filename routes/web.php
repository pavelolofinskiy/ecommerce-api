<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('/products', AdminProductController::class)->except(['show']);

Route::apiResource('/categories', AdminCategoryController::class)->except(['show']);

Route::get('/products', function () {
    return view('products');
});