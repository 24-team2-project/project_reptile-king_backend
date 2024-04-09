<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PurchaseController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exist:users,id',
            'good_id' => 'required|exist:goods,id',
            'total_price' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'payment_selection' => 'required|string',
        ]);

        $purchase = Purchase::create($request->all());
    }

    public function show(Purchase $purchase)
    {
        $purchase = Purchase::find($purchase->user_id);
        if (!$purchase) {
            return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        }
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
