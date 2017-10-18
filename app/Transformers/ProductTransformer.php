<?php

namespace App\Transformers;

use App\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Product $product)
    {
        return [
            'identifier' => (int)$product->id,
            'title' => (string)$product->name,
            'details' => (string)$product->description,
            'stock' => (int)$product->quantity,
            'situation' => (string)$product->status,
            'picture' => url("img/{$product->image}"),                                          // The url is going to go in the public folder which contains a folder called ' img ' and we should include the name of the image as a parameter which is stored in the product instance under the image attribute ie: ' $product->image ' . So it is just going to append the name of the image for the full URL. Note we are using a url() helper here. It automatically uses the path to our public folder directory in our APP.
            'seller' => (int)$product->seller_id,
            'creationDate' => (string)$product->created_at,
            'lastChange' => (string)$product->updated_at,
            'deletedDate' => isset($product->deleted_at) ? (string) $product->deleted_at : null,        // The problem with the deleted_at attribute is that it can be null at any moment. So we need to return a string representation of the date or a null value. So that is going to depend if these values is set. If the deleted_at is set, we are going to return a string representation of this ie: ' (string)$user->deleted_at ' . In case of not we are going to return null ie: ' : null ' .
            'links' => [                // The links are ' hateoas hypermedia controls ' meaning are just links that make it easy for client to access other information based in this example on the products being viewed. E.g. is if the client was viewing the Product 1, he will also get links to Product 1 categories, or Product 1 seller, or Product 1 transactions, or Product 1 buyers or even Product 1 itself(if it is viewing all Products). This makes it easy for the client to access information using our API.
                [
                    'rel' => 'self',
                    'href' => route('products.show', $product->id),              // Most of these routes need a product id to access information except the last one.
                ],
                [
                    'rel' => 'product.buyers',
                    'href' => route('products.buyers.index', $product->id),
                ],
                [
                    'rel' => 'product.categories',
                    'href' => route('products.categories.index', $product->id),
                ],
                [
                    'rel' => 'product.transactions',
                    'href' => route('products.transactions.index', $product->id),
                ],
                [
                    'rel' => 'seller',                                          // You might be wondering why we are not using for rel ' product.seller ' ? That is because remember that this route only need information about the seller and doesn't need anything related to the product.
                    'href' => route('sellers.show', $product->seller_id),
                ],
            ]
        ];
    }

    public static function originalAttribute($index)        // Going to receive the index as the transformed attribute and it's going to return the original attribute.
    {
        $attribute = [                                      // We're going to have a list of attributes and we are going to map and we are going to map every attribute to its original.
            'identifier' => 'id',
            'title' => 'name',
            'details' => 'description',
            'stock' => 'quantity',
            'situation' => 'status',
            'picture' => 'image',
            'seller' => 'seller_id',
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
            'name' => 'title',
            'description' => 'details',
            'quantity' => 'stock',
            'status' => 'situation',
            'image' => 'picture',
            'seller_id' => 'seller',
            'created_at' => 'creationDate',
            'updated_at' => 'lastChange',
            'deleted_at' => 'deletedDate',
        ];

        return isset($attribute[$index]) ? $attribute[$index] : null;       // In this way we are removing the possibility of a property we are not transforming e.g. the ' password ' . And additionally we are going to know which original attribute corresponds with which transformed attribute.
    }
}
