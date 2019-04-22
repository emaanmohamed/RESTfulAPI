<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerController extends ApiController
{
    public function index()
    {
        $buyers = Buyer::Has('transactions')->get();

        return $this->showAll($buyers);
    }

    public function show(Buyer $buyer)
    {
       // $buyer = Buyer::has('transactions')->findOrFail($id);
//        $buyer = DB::table('buyers')
//                ->select('buyers.id as B_ID', 'buyers.created_at as B_C', 'buyers.updated_at as B_U', 'products.name')
//                ->join('transactions', 'transactions.buyer_id', '=', 'buyers.id')
//                ->join('products', 'products.id', '=', 'transactions.product_id')
//                ->where('products.id', $id)
//                ->get();
        return $this->showOne($buyer);
    }
}
