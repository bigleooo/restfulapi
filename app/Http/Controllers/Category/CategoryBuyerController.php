<?php

namespace App\Http\Controllers\Category;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class CategoryBuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category)
    {                                                                                                           // When we get at the ' pluck('transactions.buyer') ' as done before, this is where it gets complicated. It is not going to work, we will not get the buyer. This is because Remember that when we obtain the transactions with pluck ' pluck('transactions') ', we are going to obtain several transactions(collections) into one product(collection) because a product may have many transactions. And if we use ' ->pluck('transactions.buyer') ' , Laravel is not going to be able to localise the buyer inside everyone of those collections. We will get a collection for each products which will include a collection for each buyer associated with the transaction that bought the product, so collection with open product[], then buyer[], followed by another buyer[] for the same product, then close array for that product, open another product[] with many buyer []........ || So first before ' ->pluck('transactions.buyer') ' we need to obtain a unique only long collection, and that can be done using the collapse() method. And now just after that we can use pluck() method ' ->pluck('buyer') ' again to obtain only the buyer for the full list of transactions.
        $buyers = $category->products()
            ->whereHas('transactions')
            ->with('transactions.buyer')
            ->get()
            ->pluck('transactions')
            ->collapse()
            ->pluck('buyer')
            ->unique('id')
            ->values();

        return $this->showAll($buyers);
    }

}
