<?php

namespace App\Transformers;

use App\Category;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Category $category)
    {
        return [
            'identifier' => (int)$category->id,
            'title' => (string)$category->name,
            'details' => (string)$category->description,
            'creationDate' => (string)$category->created_at,
            'lastChange' => (string)$category->updated_at,
            'deletedDate' => isset($category->deleted_at) ? (string) $category->deleted_at : null,        // The problem with the deleted_at attribute is that it can be null at any moment. So we need to return a string representation of the date or a null value. So that is going to depend if these values is set. If the deleted_at is set, we are going to return a string representation of this ie: ' (string)$user->deleted_at ' . In case of not we are going to return null ie: ' : null ' .
            'links' => [                // The links are ' hateoas hypermedia controls ' meaning are just links that make it easy for client to access other information based in this example on the category being viewed. E.g. is if the client was viewing the Category 1, he will also get links to Category 1 products, or category 1 sellers, or category 1 transactions, or category 1 buyers or even category 1 itself(if it is viewing all categories). This makes it easy for the client to access information using our API.
                [
                    'rel' => 'self',
                    'href' => route('categories.show', $category->id),              // All these routes need a category id to access information.
                ],
                [
                    'rel' => 'category.buyers',
                    'href' => route('categories.buyers.index', $category->id),
                ],
                [
                    'rel' => 'category.products',
                    'href' => route('categories.products.index', $category->id),
                ],
                [
                    'rel' => 'category.sellers',
                    'href' => route('categories.sellers.index', $category->id),
                ],
                [
                    'rel' => 'category.transactions',
                    'href' => route('categories.transactions.index', $category->id),
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
            'created_at' => 'creationDate',
            'updated_at' => 'lastChange',
            'deleted_at' => 'deletedDate',
        ];

        return isset($attribute[$index]) ? $attribute[$index] : null;       // In this way we are removing the possibility of a property we are not transforming e.g. the ' password ' . And additionally we are going to know which original attribute corresponds with which transformed attribute.
    }
}
