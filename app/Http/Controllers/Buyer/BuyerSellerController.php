<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerSellerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {                                                                                           // We have to use eager loading . We cannot just use ' ->with('seller') '  because we have to go through product first. This can be done using the dot notation ' . ' ie. ' ->with('product.seller') ' . This specify to Laravel that we need the list of transactions with the product of everyone of the transactions and everyone of those products is required with the seller inside. Then we get the collection ' ->get() ' . Then we pluck the seller from the collection ' ->pluck('product.seller') ' . Now we have a  detail, we can't have repeated sellers, because different products may belong to the same seller. So we need to use the unique method ' ->unique() ' from the collection, and we are going to specify the seller is different form the other depending on the id. The problem now here is if there exist a repeated seller, the unique() method is gonna remove this from the collection, but there is going to exist an empty space. So we are going to have different sellers and when its going to be a repeated one, we are going to have an empty object and that is not a very good idea of course. So for that we need Laravel values() method ' ->values() ' which will re-create the index, and in that way we are going to obtain all the list without repeated elements and without empty ones.
        $sellers = $buyer->transactions()->with('product.seller')
            ->get()
            ->pluck('product.seller')
            ->unique('id')
            ->values();

        return $this->showAll($sellers);
    }

}
