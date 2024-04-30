<?php

use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\Goods\GoodController;
use App\Http\Controllers\Goods\GoodReviewController;
use App\Http\Controllers\Goods\PurchaseController;
use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\Reptiles\CageController;
use App\Http\Controllers\Reptiles\ReptileController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Boards\PostController;
use App\Http\Controllers\Boards\CommentController;
use App\Http\Controllers\Boards\SupportController;
use App\Http\Controllers\Sensors\TemperatureHumidityController;
use App\Http\Controllers\Upload\ImageController;
use App\Http\Controllers\Categories\CategoryController;


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
    // 토큰 갱신
    Route::post('/refresh-token', [ JWTAuthController::class, 'refresh' ]);
    
    // 로그아웃
    Route::post('/logout', [ JWTAuthController::class, 'logout' ]);

    // 파충류
    Route::apiResource('reptiles', ReptileController::class)->except('create', 'edit', 'show');
    // 파충류 상세
    Route::get('/reptiles/{reptileSerialCode}', [ReptileController::class, 'show']);

    // 사육장
    Route::apiResource('cages', CageController::class)->except('create', 'edit');
    // 사육장 온습도 데이터 조회
    Route::get('/cages/{cage}/temperature-humidity', [CageController::class, 'getTempHumData']);
    // 사육장 온습도 데이터 수정
    Route::patch('/cages/{cage}/update-temperature-humidity', [CageController::class, 'updateTempHumData']);
    // 사육장 최신 온습도 데이터 조회
    Route::get('/cages/{cage}/latest-temperature-humidity', [CageController::class, 'getLatestTempHumData']);
    // 사육장 일별 온습도 데이터 조회
    Route::get('/cages/{cage}/daily-temperature-humidity', [CageController::class, 'getDailyTempHumData']);

    // // 커뮤니티
    Route::apiResource('posts', PostController::class)->except('index', 'show', 'create', 'edit', );

    // // 댓글
    Route::apiResource('comments', CommentController::class)->only('update', 'destroy');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);

    // 마켓
    Route::apiResource('goods', GoodController::class)->except('index', 'show', 'create', 'edit');

    // 상품 리뷰
    Route::apiResource('good_reviews', GoodReviewController::class)->except('create', 'edit');

    // 카테고리
    Route::apiResource('categories', CategoryController::class)->only('store', 'destroy');

    // 구매
    Route::apiResource('purchases', PurchaseController::class)->except('create', 'edit', 'update');

    // 문의
    Route::apiResource('supports', SupportController::class)->except('create', 'edit');

    // 사용자
    Route::get('/users', [UserController::class, 'index']); // 실험용 기능 없음
});

//카테고리
Route::get('/categories', [CategoryController::class, 'index']);

// // 커뮤니티
Route::get('/posts/category/{category_id}', [PostController::class, 'selectCategory']);
Route::get('/posts/search', [PostController::class, 'search']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);

// // 마켓
Route::get('/goods/search', [GoodController::class, 'search']);
Route::get('/goods', [GoodController::class, 'index']);
Route::get('/goods/{id}', [GoodController::class, 'show']);
Route::get('/goods/category/{categoryId}', [GoodController::class, 'findByCategory']);

// 온습도 데이터 저장(라즈베리파이에서 데이터 전송)
Route::post('/tnhs', [TemperatureHumidityController::class, 'store']);

// 이미지 업로드
Route::post('/upload-image', [ImageController::class, 'uploadImageForEditor']);
Route::post('/delete-images', [ImageController::class, 'deleteImagesForEditor']);
