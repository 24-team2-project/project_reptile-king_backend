<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function userFinder($nickname)
    {
        $user = User::findOrFail($nickname, ['nickname']);

        if ($user) {
            return response()->json($user, [
                'msg' => '유저 검색 결과'
            ]);
        }

        return response()->json([
            'msg' => '검색 결과가 없습니다.'
        ], 404);
    }


    public function index()
    {
        $users = User::all();

        return response()->json($users ,[
            'msg' => '유저 목록',
        ]);
    }

    public function show($id)
    {
        $user = JWTAuth::user();

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
        ];

        return response()->json($userData ,[
            'msg' => '유저 개인정보',
        ]);
    }

    public function update(Request $request)
    {
        $user = JWTAuth::user();

        // 요청 데이터 유효성 검사
        $validatedData = $request->validate([
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|json'
        ]);

        // 유저 정보 업데이트
        $user->update($validatedData);

        return response()->json([
            'message' => '유저 정보가 성공적으로 업데이트되었습니다.'
        ]);
    }


    public function destroy(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => '인증 오류'], 401);
        }

        try {
            DB::beginTransaction();
            // 사용자 데이터 삭제
            $user->delete();

            DB::commit();

            return response()->json(['message' => '회원 탈퇴 성공']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => '회원 탈퇴 실패: ' . $e->getMessage()], 500);
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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }
}
