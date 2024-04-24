<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Good;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class PurchaseController extends Controller
{
    // 구매 시 유저 정보 전달
    public function userInfo()
    {
        $user = JWTAuth::user();

        return response()->json($user);
    }

    // 구매 목록
    public function index()
    {
        $user = JWTAuth::user();

        $purchases = Purchase::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json($purchases);
    }

    public function store(Request $request)
    {
        $user = JWTAuth::user();

        $request->validate([
            'good_id' => 'required|exists:goods,id',
            'total_price' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'payment_selection' => 'required|string',
        ]);

        $reqData = $request->all();
        $reqData['user_id'] = $user->id;
        $purchase = Purchase::create($reqData);

        return response()->json($purchase, 201);
    }

    // 구매 상세
    public function show(Purchase $purchase)
    {
        // $purchase = Purchase::find($purchase->user_id);
        // if (!$purchase) {
        //     return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        // }
        return response()->json($purchase);
    }

    public function create()
    {
        //
    }

    public function edit(Purchase $purchase)
    {
        //
    }

    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return response()->json(['message' => '목록에서 삭제되었습니다.']);
    }
}
