<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {                                                                                           // Basically when we pluck the categories out of products we get a collection inside another collection. This is because transactions is a collection, and categories is another collection. Note we Laravel is ignoring product, so we get collection inside collection. We don't want this, we only want a list of Categories, so we use the collapse method ' collapse() ' . The collapse method is gonna create a unique list with several list. The collapse method is gonna create a unique collection using several collections that we have inside.
        $categories = $buyer->transactions()->with('product.categories')
            ->get()
            ->pluck('product.categories')
            ->collapse()
            ->unique('id')
            ->values();                                                                         // Using the values() method to remove the empty categories when repeated.

        return $this->showAll($categories);
    }

}
