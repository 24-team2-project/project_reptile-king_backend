<?php

namespace App\Http\Controllers;

use App\Mail\OrderShipped;
use App\Models\EmailAuthCode;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ForgetPasswordController extends Controller
{
    // 이메일 인증
    public function sendMailAuth(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'max:255', 'email:rfc, strict'],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 400);
        }

        $userEmail = $validator->safe()->only('email');

        try {
            $user = User::where('email', $userEmail)->first();

            if(empty($user)){
                return response()->json([ 'msg' => '등록되지 않은 이메일' ], 400);
            }

            $authCode = Str::random(7);
            $ttl = 180; // 3분

            Redis::setex('email_verification:'.$user->email, $ttl, $authCode);
            Mail::to($user->email)->send(new OrderShipped($authCode));
            
            return response()->json([
                'msg' => '메일 발송'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 인증코드 검사
    public function verifyAuthentication(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'max:255', 'email:rfc, strict'],
            'authCode' => ['required', 'string', 'max:7'],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 400);
        }
        
        $reqData = $validator->safe();

        try {

            $dbData = Redis::get('email_verification:'.$reqData['email']);

            $msg = '';
            $status = 200;

            if($dbData === null){
                $msg = '인증 실패 : 인증시간 초과';
                $status = 401;
            } else if($dbData !== $reqData['authCode']){
                $msg = '인증 실패 : 인증코드 불일치';
                $status = 401;
            } else{
                $msg = '인증 완료';
                Redis::del('email_verification:'.$reqData['email']);
            }

            return response()->json([
                'msg' => $msg
            ], $status);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 비밀번호 수정
    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'max:255', 'email:rfc, strict'], // 'email' => 'required|email:rfc,dns
            'password' => ['required', 'confirmed', Rules\Password::defaults()->mixedCase()->symbols()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 400);
        }

        $reqData = $validator->safe()->only('email', 'password');
        try {
            $user = User::where('email', $reqData['email'])->first();

            if(empty($user)){
                return response()->json([ 'msg' => '등록되지 않은 이메일' ], 400);
            }

            $user->password = bcrypt($reqData['password']);
            $user->save();

            return response()->json([
                'msg' => '변경 완료'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }

    }

}
