<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Upload\ImageController;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    public function userFinder($nickname)
    {
        $user = JWTAuth::user();

        try{
            $users = User::where('nickname', 'like', '%' . $nickname . '%')
            ->whereNotIn('nickname', [$user->nickname, 'administrator'])
            ->pluck('nickname');

            if ($users->isNotEmpty()) {
                return response()->json([
                    'users' => $users,
                    'msg' => '유저 검색 결과'
                ], 200);
            }

            return response()->json([
                'msg' => '검색 결과가 없습니다.'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류'
            ], 500);
        }
        
    }



    public function index()
    {
        try{
            $users = User::all();

            return response()->json([
                'msg' => '유저 목록',
                'users' => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류'
            ], 500);
        }

    }

    public function showInfo()
    {
        $user = JWTAuth::user();
        try {
    
            return response()->json([
                'msg' => '유저 개인정보',
                'user' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function update(Request $request)
    {
        $user = JWTAuth::user();

        try{
            // 요청 데이터 유효성 검사
            $validatedData = $request->validate([
                'phone' => ['nullable', 'string', 'max:20'],
                'nickname' => ['nullable', 'string', 'max:20', 'unique:users,nickname,'.$user->id],
            ]);

            DB::transaction(function () use ($user, $validatedData) {
                // 유저 정보 업데이트
                $user->update($validatedData);
            });

            return response()->json([
                'msg' => '유저 정보 업데이트 완료',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }

        
    }

    public function updateImage(Request $request){
        $user = JWTAuth::user();

        try{
            $rules = [
                'beforeImgUrl' => ['string', 'nullable'],
            ];

            $checkNewImage = false;

            if($request->hasFile('newImage')){
                $rules['newImage'] = ['required', 'image', 'mimes:jpg,jpeg,png,bmp,gif,svg,webp', 'max:2048'];
                $checkNewImage = true;
            }

            // 요청 데이터 유효성 검사
            $validated = Validator::make($request->all(), $rules);

            if($validated->fails()){
                return response()->json([
                    'msg' => 'validation error',
                    'errors' => $validated->errors(),
                ], 400);
            }
            
            $reqData = $validated->validated();

            $images = new ImageController();

            // 이전 이미지 삭제
            if(array_key_exists('beforeImgUrl', $reqData) && !is_null($reqData['beforeImgUrl'])){
                $deleteList = [$reqData['beforeImgUrl']];
                $images->deleteImages($deleteList);
            }

            if($checkNewImage){
                // 새 이미지 업로드
                $user->image = $images->getImageUrl($reqData['newImage'], 'users');
            } else{
                $user->image = null;
            }

            DB::transaction(function () use ($user) {
                $user->save();
            });

            return response()->json([
                'msg' => '이미지 업데이트 완료',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyUser()
    {
        $user = JWTAuth::user();

        // try {
        //     DB::beginTransaction();
        //     // 사용자 데이터 삭제
        //     $user->delete();

        //     DB::commit();

        //     return response()->json(['message' => '회원 탈퇴 성공']);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json(['error' => '회원 탈퇴 실패: ' . $e->getMessage()], 500);
        // }

        try{
            DB::transaction(function () use ($user) {
                // 사용자 데이터 삭제
                $user->delete();
            });

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류, 탈퇴 실패',
                'error' => $e->getMessage(),
            ], 500);
        }

        // try {
        //     DB::beginTransaction();

        //     // 사용자의 게시물 삭제
        //     $posts = $user->posts;
        //     foreach ($posts as $post) {
        //         $this->deleteComments($post->comments); // 게시물의 댓글과 답글 삭제
        //         $post->delete(); // 게시물 삭제
        //     }

        //     // 사용자의 댓글 삭제
        //     $comments = $user->comments;
        //     $this->deleteComments($comments);

        //     // 사용자의 문의글 삭제
        //     $supports = $user->supports;
        //     $supports->delete();

        //     // 사용자의 구매 목록 삭제
        //     $purchases = $user->purchases;
        //     $purchases->delete();

        //     // 사용자의 리뷰 삭제
        //     $goodReviews = $user->goodReviews;
        //     $goodReviews->delete();

        //     // 사용자 데이터 삭제
        //     $user->delete();

        //     DB::commit();

        //     return response()->json(['message' => '회원 탈퇴 성공']);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json(['error' => '회원 탈퇴 실패: ' . $e->getMessage()], 500);
        // }
    }

    private function deleteComments($comments)
    {
        foreach ($comments as $comment) {
            if ($comment->parent_comment_id === null) { // 댓글인 경우
                $this->deleteComments($comment->replies); // 해당 댓글의 답글 삭제
            }
            $comment->delete(); // 댓글 또는 답글 삭제
        }
    }

}
