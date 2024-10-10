<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = JWTAuth::user();

        try {
            $addresses = $user->addresses;

            return response()->json([
                'msg' => '성공',
                'data' => $addresses,
            ]);
        } catch (\Exception $e) {
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
            $user->addresses()->create([
                'zipcode' => $request->zipcode,
                'address' => $request->address,
                'detail_address' => $request->detailAddress,
            ]);

            return response()->json([
                'msg' => '등록 완료',
            ], 201);
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

        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'msg' => '주소가 존재하지 않습니다.',
            ], 404);
        }

        try {
            $address->update([
                'zipcode' => $request->zipcode,
                'address' => $request->address,
                'detail_address' => $request->detailAddress,
            ]);

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

        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'msg' => '주소가 존재하지 않습니다.',
            ], 404);
        }

        try {
            $address->delete();

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
