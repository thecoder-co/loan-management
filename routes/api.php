<?php

use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\LoanController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// JWT-Protected Routes
Route::middleware('auth:api')->group(function () {
    Route::get('profile', [CustomerController::class, 'profile']);
    // Balance & transactions
    Route::post('balance/add', [CustomerController::class, 'addBalance']);
    Route::get('transactions', [CustomerController::class, 'transactions']);
    Route::post('pay-service', [CustomerController::class, 'payService']);


    // Loan operations
    Route::controller(LoanController::class)->group(function () {
        Route::post('loans', 'takeLoan');
        Route::get('loans', 'index');
        Route::get('loans/{loan}', 'show');
        Route::post('loans/{loan}/repay', 'repay');
    });
});

Route::controller(CustomerController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

// Route::get('test-token', function (Request $request) {
//     try {
//         $user = JWTAuth::parseToken()->authenticate();
//         return response()->json(['user' => $user]);
//     } catch (Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 401);
//     }
// });
