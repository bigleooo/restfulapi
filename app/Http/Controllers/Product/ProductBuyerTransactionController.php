<?php

namespace App\Http\Controllers\Product;

use App\Buyer;
use App\Product;
use App\Seller;
use App\Transaction;
use App\Transformers\TransactionTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();          // As we are using in some of our controllers the parent constructor, we need to create a constructor in ' ApiController ' that is the Parent Class, in order to avoid any kind of error.

        $this->middleware('transform.input:' . TransactionTransformer::class)->only(['store']);        // Register the middleware. This middleware is going to receive a new parameter i.e. the name of the transformer or basically the name of the class for the transformer. In the case we are using instances of transaction, we are going to use the Transaction Transformer and it is going to be executed only for the store() and update() methods found in this controller.
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $rules = [
            'quantity' => 'required|integer|min:1',
        ];

        $this->validate($request, $rules);

        if ($buyer->id == $product->seller_id) {
            return $this->errorResponse('The buyer must be different from the seller', 409);
        }

        if (!$buyer->isVerified()) {
            return $this->errorResponse('The buyer must be a verified user', 409);
        }

        if (!$product->seller->isVerified()) {
            return $this->errorResponse('The seller must be a verified user', 409);
        }

        if (!$product->isAvailable()) {
            return $this->errorResponse('The product is not available', 409);
        }

        if ($product->quantity < $request->quantity) {
            return $this->errorResponse('The products does not have enough units for this transaction', 409);
        }

        return DB::transaction(function() use ($request, $product, $buyer) {                    // If while this user is doing this transaction, another user is buying the same product and the product we need to be sure that the quantities and the operation are going to perform sequentially. Basically because the quantity of the product is gonna change and that can affect the transaction that comes later. For this we need to use a database transaction preferably and it is gonna allow us that if in some point the transaction or the creation of the transaction fails, everything is gonna keep the same automatically, Laravel and the database transaction is gonna rollback the database to its previous status. So we are gonna return the result of the database transaction using the DB Facade ' DB:: ' . The facade will use the transaction method ' DB::transaction() ' . The transaction method will basically receive a function that needs to use the $request, $product, $buyer. This function is gonna reduce the quantity of the product depending on the $request, and just save. If it works properly we just need to create the new transaction using the Transaction (our Model Transaction) facade ' Transaction::create(); ' . There we just need to specify the quantity, the buyer id, and the product id. After this we just need to return the new instance for this function.
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([                                                // If something goes wrong in the function e.g. cannot create the new instance of transaction, Laravel and the DB Facade will roll back the product quantity that it set in the previous line. Basically it will roll back everything in the function. Cool eh?
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return $this->showOne($transaction, 201);
        });

    }

}
