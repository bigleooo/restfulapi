<?php

namespace App\Transformers;

use App\Seller;
use League\Fractal\TransformerAbstract;

class SellerTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Seller $seller)                             // For example here we don't want the ' $user->admin ' attribute from being displayed in our transformation of the buyer instance. So we just remove the line " 'isAdmin' => ($buyer->admin === 'true'), " that we put for example in our ' UserTransformer ' so that we do not include this in the final response of the transformer.
    {
        return [
            'identifier' => (int)$seller->id,
            'name' => (string)$seller->name,
            'email' => (string)$seller->email,
            'isVerified' => (int)$seller->verified,
            'creationDate' => (string)$seller->created_at,
            'lastChange' => (string)$seller->updated_at,
            'deletedDate' => isset($seller->deleted_at) ? (string) $seller->deleted_at : null,        // The problem with the deleted_at attribute is that it can be null at any moment. So we need to return a string representation of the date or a null value. So that is going to depend if these values is set. If the deleted_at is set, we are going to return a string representation of this ie: ' (string)$user->deleted_at ' . In case of not we are going to return null ie: ' : null ' .
            'links' => [                // The links are ' hateoas hypermedia controls ' meaning are just links that make it easy for client to access other information based in this example on the Seller being viewed. E.g. is if the client was viewing the Seller 1, he will also get links to Seller 1 products, or Seller 1 buyers, or Seller 1 transactions, or Seller 1 categories or even Seller 1 itself(if it is viewing all categories). This makes it easy for the client to access information using our API.
                [
                    'rel' => 'self',
                    'href' => route('sellers.show', $seller->id),              // All these routes need a seller id to access information.
                ],
                [
                    'rel' => 'seller.categories',
                    'href' => route('sellers.categories.index', $seller->id),
                ],
                [
                    'rel' => 'seller.products',
                    'href' => route('sellers.products.index', $seller->id),
                ],
                [
                    'rel' => 'seller.buyers',
                    'href' => route('sellers.buyers.index', $seller->id),
                ],
                [
                    'rel' => 'seller.transactions',
                    'href' => route('sellers.transactions.index', $seller->id),
                ],
                [
                    'rel' => 'user',
                    'href' => route('users.show', $seller->id),
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
