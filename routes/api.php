<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::apiResource('/users', UserController::class);
        Route::apiResource('/orders', OrderController::class);
        Route::apiResource('/order_items', OrderItemController::class);
        Route::apiResource('/products', ProductController::class);
    });
});
