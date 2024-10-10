<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = JWTAuth::user();

        try {
            $payments = $user->payments;

            return response()->json([
                'msg' => '성공',
                'data' => $payments,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
            ], 500);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = JWTAuth::user();

        try {
            $findPayment = $user->payments()->where('number', $request->number)->first();  // 중복된 카드번호가 있는지 확인

            if ($findPayment) {
                return response()->json([
                    'msg' => '이미 등록된 카드입니다.',
                ], 400);
            }
            else {

                $storeList = [
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'number' => $request->number,
                    'month' => $request->month,
                    'year' => $request->year,
                ];

                if($user->payments->isEmpty()) {
                    $storeList['is_default'] = true;
                }

                $user->payments()->create($storeList);

                return response()->json([
                    'msg' => '등록 완료',
                ], 201);

            }

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = JWTAuth::user();

        try {
            $payment = $user->payments()->find($id);

            if (!$payment) {
                return response()->json([
                    'msg' => '카드가 존재하지 않습니다.',
                ], 404);
            }



            $payment->update([
                'name' => $request->name,
                'number' => $request->number,
                'month' => $request->month,
                'year' => $request->year,
                'is_default' => $request->isDefault,
            ]);

            for ($i = 0; $i < count($user->payments); $i++) {
                if ($user->payments[$i]->id != $payment->id) {
                    $user->payments[$i]->update([
                        'is_default' => false,
                    ]);
                }
            }


            return response()->json([
                'msg' => '수정 완료',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
            ], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = JWTAuth::user();

        try {
            $payment = $user->payments()->find($id);

            if (!$payment) {
                return response()->json([
                    'msg' => '카드가 존재하지 않습니다.',
                ], 404);
            }

            $payment->delete();


            if($user->payments->count() == 1) {
                $user->payments[0]->update([
                    'is_default' => true,
                ]);
            }


            return response()->json([
                'msg' => '삭제 완료',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
            ], 500);
        }   
    }
}
