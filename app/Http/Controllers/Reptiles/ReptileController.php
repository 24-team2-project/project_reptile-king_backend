<?php

namespace App\Http\Controllers\Reptiles;

use App\Http\Controllers\Controller;
use App\Models\Reptile;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReptileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = JWTAuth::user();
        return response()->json([
            'msg' => '확인용'
        ]);
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
        //
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
