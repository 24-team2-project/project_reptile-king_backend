<?php

use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\Goods\GoodController;
use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\Reptiles\ReptileController;
use App\Http\Controllers\Users\UserController;
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

// 회원가입
Route::post('/register',[ RegisterUserController::class, 'register' ]);
Route::post('/register/check-email',[ RegisterUserController::class, 'checkedEmail' ]);
Route::post('/register/check-nickname',[ RegisterUserController::class, 'checkedNickname' ]);

// 비밀번호 재설정
Route::post('/forget-password',[ ForgetPasswordController::class, 'sendMailAuth' ]);
Route::post('/forget-password/verify-auth',[ ForgetPasswordController::class, 'verifyAuthentication' ]);
Route::patch('/forget-password/change-password',[ ForgetPasswordController::class, 'changePassword' ]);

// 로그인
Route::post('/login', [ JWTAuthController::class, 'login' ]);

// jwt토큰 인증이 필요한 라우터들
Route::group([ 'middleware' => 'jwt.auth'], function(){ 
    // 로그아웃
    Route::post('/logout', [ JWTAuthController::class, 'logout' ]);

    // 펫
    Route::apiResource('reptiles', ReptileController::class);


    
    
    Route::get('/users', [UserController::class, 'index']); // 실험용 기능 없음
}); 

// 마켓
Route::apiResource('goods', GoodController::class);

