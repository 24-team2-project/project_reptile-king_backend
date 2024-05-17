<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users ,[
            'msg' => '유저 목록',
        ]);
    }

    public function show()
    {
        $user = JWTAuth::user();

        return response()->json($user ,[
            'msg' => '유저 개인정보',
        ]);
    }

    public function update(Request $request)
    {
        $user = JWTAuth::user();

    }

    public function destroy(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => '인증 오류'], 401);
        }

        try {
            DB::beginTransaction();

            // 사용자의 게시물 삭제
            $posts = $user->posts;
            foreach ($posts as $post) {
                $this->deleteComments($post->comments); // 게시물의 댓글과 답글 삭제
                $post->delete(); // 게시물 삭제
            }

            // 사용자의 댓글 삭제
            $comments = $user->comments;
            $this->deleteComments($comments);

            // 사용자의 문의글 삭제
            $supports = $user->supports;
            $supports->delete();

            // 사용자 데이터 삭제
            $user->delete();

            DB::commit();

            return response()->json(['message' => '회원 탈퇴 성공']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => '회원 탈퇴 실패: ' . $e->getMessage()], 500);
        }
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
