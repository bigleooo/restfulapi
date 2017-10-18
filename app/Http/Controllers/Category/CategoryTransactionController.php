<?php

namespace App\Http\Controllers\Category;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class CategoryTransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category)
    {                                                                                                                       // There exist products that don't have transactions because there is products that never had a sale, so no transaction. So we need to be sure to deal only with the products that have any transactions. So here is the deal. We already know that all the products have a relationship called ' transactions ' , but we don't know if all the products have a transactions. If we use ' ->with('transaction') ' we are obtaining all the transactions including those products that do not have any transactions. So before to do this we need to make sure that only the products that have at least 1 transaction, and that can be done by using the whereHas() method ' ->whereHas('transactions') ' . With this we already know that after this line we obtain only the products with at least 1 transaction. So when we use the ' ->with('transactions') ' we are loading the transactions only for the products that have at least 1 transaction.
        $transactions = $category->products()
            ->whereHas('transactions')
            ->with('transactions')
            ->get()
            ->pluck('transactions')
            ->collapse();

        return $this->showAll($transactions);
    }

}
