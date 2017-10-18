<?php

namespace App\Http\Controllers\Seller;

use App\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class SellerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sellers = Seller::has('products')->get();

        return $this->showAll($sellers);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Seller $seller)            // Using Model Binding. Therefore need to implement a Global Scope to ensure that the instance of Seller, $seller is actually a user/seller using the query ' Seller::has('products') ' so that this user actually has one or more products.
    {
        return $this->showOne($seller);
    }
}
