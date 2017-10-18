<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $buyers = Buyer::has('transactions')->get();        // $buyers here is a Collection of instances of the buyer Model.

        return $this->showAll($buyers);                             // All multi-result sets returned by Eloquent are instances of the  Illuminate\Database\Eloquent\Collection object, including results retrieved via the get method or accessed via a relationship.

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Buyer $buyer)          // Using implicit model binding here. But we need to add the restriction of ' Buyer::has('transaction') ' or we will get any user instead of a user/buyer with at least 1 transaction. For this we will use something called global scopes. The Global scopes are basically queries or parts of the query that we can automatically add for the operations over a specific model. The scopes can be created in whatever directory that we need but to be consistent we are gonna create a Scopes directory, and inside we are gonna create the buyer scope.
    {
//        $buyer = Buyer::has('transactions')->findOrFail($id);       // $buyer here is an instance of the buyer Model.
        return $this->showOne($buyer);

    }

}
