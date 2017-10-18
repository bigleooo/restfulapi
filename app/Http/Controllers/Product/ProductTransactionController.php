<?php

namespace App\Http\Controllers\Product;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ProductTransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        $transactions = $product->transactions;                 // Note some products do not have transactions as they have not been bought by a buyer as of yet, so they will return an empty collection ie: { data: [] }

        return $this->showAll($transactions);
    }

}
