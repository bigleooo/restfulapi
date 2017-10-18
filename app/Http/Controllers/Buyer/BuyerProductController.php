<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {                                                                                                                // When we access the relationships ' transaction ' we are gonna get a collection of transactions and not an instance of transactions for that buyer. Basically what we need to do is go inside the collection of transactions and go transaction by transaction and obtain the product. this could be a very difficult process but fortunately Laravel help us with this through the usage of the eager loading. For this we need to obtain directly the query builder from the relationship method ' ->transactions() ' and not the relationship itself ' ->transactions ' , and we can just do it calling directly the method instead of the relationship. With this we can just say ' ->with('product') '. In that way we are obtaining the list of transactions, and every transaction is gonna come with the respective product. Basically Laravel is gonna call directly the product relationship inside every transaction of this collection. Then we just need to obtain this ' ->get() ' . So basically what we will get is all the transactions and within each transaction, we will get the product associated with this transaction. But we don't want that, we only want the list of products. So for that Laravel provides us with an additional method, this time is a method for the collections because after this point ' ->get() ' , we already have a collection of laravel, so we just need to use a new method called ' pluck() ' . The pluck method is basically going to go inside the collection and obtain an index that we explicitly give it, in this case is ' product ' ie. ' ->pluck('product') ' . So with the pluck() method we are gonna ignore the other parts of the collection and obtain only the products. So if we check again with Postman, we can now see only the list of products associated with a buyer.
        $products = $buyer->transactions()->with('product')
            ->get()
            ->pluck('product');

        return $this->showAll($products);
    }

}
