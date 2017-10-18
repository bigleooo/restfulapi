<?php

namespace App\Transformers;

use App\Transaction;
use League\Fractal\TransformerAbstract;

class TransactionTransfomer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Transaction $transaction)
    {
        return [
            'identifier' => (int)$transaction->id,
            'quantity' => (int)$transaction->quantity,
            'buyer' => (int)$transaction->buyer_id,
            'product' => (int)$transaction->product_id,
            'creationDate' => (string)$transaction->created_at,
            'lastChange' => (string)$transaction->updated_at,
            'deletedDate' => isset($transaction->deleted_at) ? (string) $transaction->deleted_at : null,        // The problem with the deleted_at attribute is that it can be null at any moment. So we need to return a string representation of the date or a null value. So that is going to depend if these values is set. If the deleted_at is set, we are going to return a string representation of this ie: ' (string)$user->deleted_at ' . In case of not we are going to return null ie: ' : null ' .
            'links' => [                // The links are ' hateoas hypermedia controls ' meaning are just links that make it easy for client to access other information based in this example on the transaction being viewed. E.g. is if the client was viewing the transaction 1, he will also get links to transaction 1 products, or transaction 1 sellers, or transaction 1 categories, or transaction 1 buyers or even transaction 1 itself(if it is viewing all categories). This makes it easy for the client to access information using our API.
                [
                    'rel' => 'self',
                    'href' => route('transactions.show', $transaction->id),              // Most of these routes need a transaction id to access information except the last 2.
                ],
                [
                    'rel' => 'transaction.categories',
                    'href' => route('transactions.categories.index', $transaction->id),
                ],
                [
                    'rel' => 'transaction.seller',          // Only one seller not a list of sellers.
                    'href' => route('transactions.sellers.index', $transaction->id),
                ],
                [
                    'rel' => 'buyer',
                    'href' => route('buyers.show', $transaction->buyer_id),
                ],
                [
                    'rel' => 'product',
                    'href' => route('products.show', $transaction->product_id),
                ],
            ]
        ];
    }

    public static function originalAttribute($index)        // Going to receive the index as the transformed attribute and it's going to return the original attribute.
    {
        $attribute = [                                      // We're going to have a list of attributes and we are going to map and we are going to map every attribute to its original.
            'identifier' => 'id',
            'quantity' => 'quantity',
            'buyer' => 'buyer_id',
            'product' => 'product_id',
            'creationDate' => 'created_at',
            'lastChange' => 'updated_at',
            'deletedDate' => 'deleted_at',
        ];

        return isset($attribute[$index]) ? $attribute[$index] : null;       // In this way we are removing the possibility of a property we are not transforming e.g. the ' password ' . And additionally we are going to know which attribute in the transformation used from the original model.
    }

    public static function transformedAttribute($index)        // Going to receive the index as the original attribute and it's going to return the transformed attribute.
    {
        $attribute = [                                      // We're going to have a list of attributes and we are going to map every original attribute to its transformed one.
            'id' => 'identifier',
            'quantity' => 'quantity',
            'buyer_id' => 'buyer',
            'product_id' => 'product',
            'created_at' => 'creationDate',
            'updated_at' => 'lastChange',
            'deleted_at' => 'deletedDate',
        ];

        return isset($attribute[$index]) ? $attribute[$index] : null;       // In this way we are removing the possibility of a property we are not transforming e.g. the ' password ' . And additionally we are going to know which original attribute corresponds with which transformed attribute.
    }
}
