<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterUserController extends Controller
{
    
    public function register(RegisterUserRequest $request){

        try {
            $request->validated(); // 유효성 검사

        } catch (ValidationException $e) {
            return response()->json([
                'msg'              => '유효성 검사 오류',
                'error' => $e->getMessage()
            ], 400);
        }

        $validated = $request->safe(); // 잠재적 위험요소 제거 및 방지(XSS)

        try {
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => bcrypt($validated['password']),
                'nickname'  => $validated['nickname'],
                'address'   => $validated['address'],
                'phone'     => $validated['phone'],
            ]);

            $role = Role::where('role', 'post_create')->first();
            $user->roles()->attach($role, ['created_at' => now()]);

            return response()->json([
                'msg' => '성공'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkedEmail(Request $request){
        $validator = Validator::make( $request->all(), [
            'email' => 'unique:users,email',
        ]);

        return response()->json([
            'msg' => $validator->fails() ? '중복' : '가능'
        ], 200);
    }

    public function checkedNickname(Request $request){
        $validator = Validator::make( $request->all(), [
            'nickname' => 'unique:users,nickname',
        ]);

        return response()->json([
            'msg' => $validator->fails() ? '중복' : '가능'
        ], 200);
    }

}
