<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterUserController extends Controller
{
    
    public function register(RegisterUserRequest $request){

        $validated = $request->validated();
        $validated = $request->safe();

        try {
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'nickname'  => $validated['nickname'],
                'address'   => $validated['address'],
                'phone'     => $validated['phone'],
            ]);

            $role = Role::where('role', 'post_create')->first();
            $user->roles()->attach($role, ['created_at' => now()]);

            return response()->json([
                'msg' => '성공'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '실패'
            ]);
        }
    }

    public function checkedEmail(Request $request){
        $validator = Validator::make( $request->all(), [
            'email' => 'unique:users,email',
        ]);

        if($validator->fails()){
            return response()->json([
                'msg' => '중복입니다'
            ]);
        }

        return response()->json([
            'msg' => '사용 가능'
        ]);

    }

    public function checkedNickname(Request $request){
        $validator = Validator::make( $request->all(), [
            'nickname' => 'unique:users,nickname',
        ]);

        if($validator->fails()){
            return response()->json([
                'msg' => '중복입니다'
            ]);
        }

        return response()->json([
            'msg' => '사용 가능'
        ]);
    }

}
