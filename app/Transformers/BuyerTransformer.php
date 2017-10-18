<?php

namespace App\Transformers;

use App\Buyer;
use League\Fractal\TransformerAbstract;

class BuyerTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Buyer $buyer)                             // For example here we don't want the ' $user->admin ' attribute from being displayed in our transformation of the buyer instance. So we just remove the line " 'isAdmin' => ($buyer->admin === 'true'), " that we put for example in our ' UserTransformer ' so that we do not include this in the final response of the transformer.
    {
        return [
            'identifier' => (int)$buyer->id,
            'name' => (string)$buyer->name,
            'email' => (string)$buyer->email,
            'isVerified' => (int)$buyer->verified,
            'creationDate' => (string)$buyer->created_at,
            'lastChange' => (string)$buyer->updated_at,
            'deletedDate' => isset($buyer->deleted_at) ? (string) $buyer->deleted_at : null,        // The problem with the deleted_at attribute is that it can be null at any moment. So we need to return a string representation of the date or a null value. So that is going to depend if these values is set. If the deleted_at is set, we are going to return a string representation of this ie: ' (string)$user->deleted_at ' . In case of not we are going to return null ie: ' : null ' .
            'links' => [                // The links are ' hateoas hypermedia controls ' meaning are just links that make it easy for client to access other information based in this example on the Buyer being viewed. E.g. is if the client was viewing the Buyer 1, he will also get links to Buyer 1 products, or Buyer 1 sellers, or Buyer 1 transactions, or Buyer 1 categories or even Buyer 1 itself(if it is viewing all categories). This makes it easy for the client to access information using our API.
                [
                    'rel' => 'self',
                    'href' => route('buyers.show', $buyer->id),              // All these routes need a buyer id to access information.
                ],
                [
                    'rel' => 'buyer.categories',
                    'href' => route('buyers.categories.index', $buyer->id),
                ],
                [
                    'rel' => 'buyer.products',
                    'href' => route('buyers.products.index', $buyer->id),
                ],
                [
                    'rel' => 'buyer.sellers',
                    'href' => route('buyers.sellers.index', $buyer->id),
                ],
                [
                    'rel' => 'buyer.transactions',
                    'href' => route('buyers.transactions.index', $buyer->id),
                ],
                [
                    'rel' => 'user',
                    'href' => route('users.show', $buyer->id),
                ],
            ]
        ];
    }

    public static function originalAttribute($index)        // Going to receive the index as the transformed attribute and it's going to return the original attribute.
    {
        $attribute = [                                      // We're going to have a list of attributes and we are going to map and we are going to map every attribute to its original.
            'identifier' => 'id',
            'name' => 'name',
            'email' => 'email',
            'isVerified' => 'verified',
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
            'name' => 'name',
            'email' => 'email',
            'verified' => 'isVerified',
            'created_at' => 'creationDate',
            'updated_at' => 'lastChange',
            'deleted_at' => 'deletedDate',
        ];

        return isset($attribute[$index]) ? $attribute[$index] : null;       // In this way we are removing the possibility of a property we are not transforming e.g. the ' password ' . And additionally we are going to know which original attribute corresponds with which transformed attribute.
    }
}
