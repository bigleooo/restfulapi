<?php

namespace App\Http\Controllers\Product;

use App\Category;
use App\Product;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ProductCategoryController extends ApiController
{
    // Here we don't need to use our middleware ' transform.input ' as even though it has an update() method, but it is not performing any kind of validation, in fact it is not creating anything, so here we don't need to use our middleware.

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        $categories = $product->categories;

        return $this->showAll($categories);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product, Category $category)          // We are going to interact directly with many-many relationships.
    {                                                              // To interact with many-many relationships we can use attach(), sync(), syncWithoutDetaching()
        $product->categories()->syncWithoutDetaching([$category->id]);        // we can add several id's if we want, but in this case we only want to add this one category id. could have ie: ' ->attach([$id,$id2,$id3,$id4]) ' . The only problem with the attach() method is that it allows us to add a category, but if we add the same category again, it will add it, making a duplication of the same category id in the pivot table. We don't want that, we want only unique category id's for a product. Now with the sync() method, it will add a new category and not duplicate any category id for a product, but the only problem is it will detach all category id's for a product and attach the specified one, making the product only have one category. This is worst than before and again is not what we want. With the syncWithoutDetaching() method we adding the new category id to the product without detaching the previous category id's. It also doesn't allow the adding of an existing category id to a product as it also syncs to the product. So the ' syncWithoutDetaching() ' method is the one we want here.

        return $this->showAll($product->categories);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Category $category)
    {
        if (!$product->categories()->find($category->id)) {
            return $this->errorResponse('The specified category is not a category of this product', 404);
        }

        $product->categories()->detach([$category->id]);

        return $this->showAll($product->categories);
    }

}
