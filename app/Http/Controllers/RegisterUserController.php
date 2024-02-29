<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterUserController extends Controller
{
    
    public function register(RegisterUserRequest $request){
        $validated = $request->validated();
        $validated = $request->safe();

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                // 'password' => $validated['password'],
                'nickname' => $validated['nickname'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
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

        // $ability = (Role::all())->filter(fn ($role) => $role->name !== 'admin')->pluck('name')->toArray();

        // $token_name = 'user-'.$user->id.'-api-token';
        // $user->createToken($token_name, $ability);
    }
}
