<?php

namespace App\Http\Controllers\Reptiles;

use App\Http\Controllers\Controller;
use App\Models\Reptile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReptileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = JWTAuth::user();

        try {
            $reptiles = $user->reptiles();

            return response()->json([
                'msg' => '확인용',
                'reptiles' => $reptiles
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '불러오기 오류',
                'error' => $e->getMessage()
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

    // 파충류 등록
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nickname' => ['required'],
            'species' => ['required'],
            'gender' => ['required', 'max:1', 'in:M,F'],
            'age'   => ['integer', 'min:0'],
            'memo' => ['string']
        ]);

        if($validator->fails()){
            return response()->json([
                'msg' => '유효성 검사 오류',
                'error' => $validator->errors()->all()
            ], 401);
        }

        $user = JWTAuth::user();
        $validator = $validator->safe();

        try {
            Reptile::create([
                'user_id' => $user->id,
                'nickname' => $validator['nickname'],
                'species' => $validator['species'],
                'gender' => $validator['gender'],
                'age' => $validator['age'],
                'memo' => $validator['memo'],
            ]);

            return response()->json([
                'msg' => '등록 완료',
            ], 201);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '오류 발생',
                'error' => $e->getMessage()
            ], 500);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show(Reptile $reptile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reptile $reptile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reptile $reptile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reptile $reptile)
    {
        //
    }
}
