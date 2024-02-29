<?php

use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register',[ RegisterUserController::class, 'register' ]);

// Route::group(['middleware' => 'auth:api'], function($router){
//     Route::post('/login', [ JWTAuthController::class, 'login' ]);
// });

Route::post('/login', [ JWTAuthController::class, 'login' ]);

Route::group([ 'middleware' => 'jwt.auth'], function(){ 
    Route::get('/users', [UserController::class, 'index']);
    
}); 

