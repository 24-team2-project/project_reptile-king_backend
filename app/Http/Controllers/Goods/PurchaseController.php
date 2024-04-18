<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class PurchaseController extends Controller
{
    public function index()
    {
        $user = JWTAuth::user();
        $purchases = Purchase::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json($purchases);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {   
        $user = JWTAuth::user();
        $request->validate([
            'good_id' => 'required|exists:goods, id',
            'total_price' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'payment_selection' => 'required|string',
        ]);

        $reqData = $request->only(['good_id', 'total_price', 'quantity', 'payment_selection']);
        $reqData['user_id'] = $user->id;
        $purchase = Purchase::create($reqData);
        
        return response()->json($purchase, 201);
    }

    public function show(Purchase $purchase)
    {
        // $purchase = Purchase::find($purchase->user_id);
        // if (!$purchase) {
        //     return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        // }
        return response()->json($purchase);
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
        return response()->json(['message' => '상품 구매가 취소되었습니다.']);
    }
}
