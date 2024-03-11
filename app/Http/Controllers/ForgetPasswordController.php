<?php

namespace App\Http\Controllers;

use App\Mail\OrderShipped;
use App\Models\EmailAuthCode;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ForgetPasswordController extends Controller
{
    public function sendMailAuth(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'max:255', 'email:rfc, strict'],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 401);
        }

        $user_email = $validator->safe()->only('email');

        try {
            $user = User::where('email', $user_email)->first();

            if(empty($user)){
                return response()->json([ 'msg' => '등록되지 않은 이메일 입니다.' ], 401);
            }

            $search = EmailAuthCode::where('email', $user->email)->first();
            
            if(!empty($search)){
                EmailAuthCode::destroy($search->id);
            }

            $auth_code = Str::random(7);
            Mail::to($user->email)->send( new OrderShipped($auth_code));
            
            EmailAuthCode::create([
                    'email'      => $user->email,
                    'auth_code'  => $auth_code,
                    'created_at' => now(),
                    'expired_at' => now()->addMinutes(3)
                    ]);

            return response()->json([
                'msg' => '메일 발송'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '이메일 인증오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyAuthentication(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => ['required', 'string', 'max:255', 'email:rfc, strict'],
            'auth_code' => ['required', 'string', 'max:7'],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 401);
        }
        
        $reqData = $validator->safe();

        try {
            $dbData = EmailAuthCode::where('email', $reqData['email'])->first();
            if(empty($dbData)){
                return response()->json([
                    'msg' => '인증번호를 발급하지 않은 이메일주소',
                ], 500);
            }

            if(now() > $dbData->expired_at){
                return response()->json([
                    'msg' => '인증 실패 : 인증시간 초과.',
                ], 401);

            } else if($dbData->auth_code !== $reqData['auth_code']){
                return response()->json([
                    'msg' => '인증 실패 : 인증코드가 일치하지 않습니다.',
                ], 401);

            } else{    
                return response()->json([
                    'msg' => '인증 완료',
                ], 200);

            }

        } catch (Exception $e) {
            return response()->json([
                'msg' => '인증코드 인증오류',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => ['required', 'string', 'max:255', 'email:rfc, strict'],
            'password' => ['required', 'confirmed',Rules\Password::defaults()->mixedCase()->symbols() ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 401);
        }

        $reqData = $validator->safe()->only('email', 'password');

        try {
            $user = User::where('email', $reqData['email'])->first();

            if(empty($user)){
                return response()->json([ 'msg' => '등록되지 않은 이메일 입니다.' ], 401);
            }

            $user->password = Hash::make($reqData['password']);
            $user->save();
            EmailAuthCode::where('email', $user->email)->delete();

            return response()->json([
                'msg' => '비밀번호 변경완료'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '비밀번호 변경오류',
                'error' => $e->getMessage()
            ], 500);
        }

    }

}
