<?php

namespace App\Http\Controllers\Seller;

use App\Product;
use App\Seller;
use App\Transformers\ProductTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    public function __construct()
    {
        parent::__construct();          // As we are using in some of our controllers the parent constructor, we need to create a constructor in ' ApiController ' that is the Parent Class, in order to avoid any kind of error.

        $this->middleware('transform.input:' . ProductTransformer::class)->only(['store', 'update']);        // Register the middleware. This middleware is going to receive a new parameter i.e. the name of the transformer or basically the name of the class for the transformer. In the case we are using instances of product, we are going to use the Product Transformer and it is going to be executed only for the store() and update() methods found in this controller.
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $products = $seller->products;

        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $seller)                   // If a User is trying to publish for the first time his first product, well it is not going to be possible because he is not currently a seller. So instead of a Seller $seller, we need to receive a User ie: User $seller as parameters in the function. In this way we are going to allow to create products for those users that do not have yet any product published.
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $data['status'] = Product::UNAVAILABLE_PRODUCT;
//        $data['image'] = '1.jpg';                               // For now we gonna use a static image that we already have in our images folder.
        $data['image'] = $request->image->store('');               // We have fixed it now. Laravel automatically knows that it is a file, so it provides us several methods that we can use. One of these methods is the ' store() ' method. The store() method receives several parameters, the path, and optionally the filesystem to use for example ' images ' ie: ' ->store('path', 'images'); ' as we set it in our filesystem file, but ' images ' is already our default filesystem so we don't need the second parameter. The path is calculated relatively from the filesystem configuration. In our case the path is gonna be calculated from the ' public path('img') ' folder. So basically we don't need to specify any folder/directory, we just need to leave it empty and Laravel is gonna automatically generate a name for that folder for that image and store it in that location. So this method is gonna return the name of that new image and we just need to assign this as we already know. Note in order to store images in the ' img ' folder in our public directory you need to configure the ' filesystem ' file to use that directory. check the lecture.
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'name' => 'required',
            'status' => 'in:' . Product::AVAILABLE_PRODUCT . ',' . Product::UNAVAILABLE_PRODUCT,            // 'in:available,unavailable'
            'image' => 'image',
        ];

        $this->validate($request,$rules);

        $this->checkSeller($seller, $product);

        $product->fill($request->intersect([                    // Because we are not sure of these values, we need to use the intersect method to ignore the null or empty values.
            'name',
            'description',
            'quantity'
        ]));

        if ($request->has('status')) {
            $product->status = $request->status;

            if ($product->isAvailable() && $product->categories()->count() == 0) {                                                  // By default when a product is created (as per above store method) the status is set to unavailable. So if here when updating the status is available but this product doesn't have a category, than the product will not be allowed to be updated with an error message outputted.
                return $this->errorResponse('An active product must have at least one category', 409);              // The code 409 specifies a conflict.
            }
        }

        if ($request->hasFile('image')) {           // Note that usually when we Update an instance using postman we have to use as body ' x-www-form-urlencoded ' format as well as the PUT/PATCH method. But for ' x-www-form-urlencoded ' format we cannot send a FILE (the image in this case) , so we need to use a the body format ' form-data ' with the method POST along with an extra input field ( Note that name, description, quantity, image is an input field) called ' _method ' . This field should have the value ' put ' or ' patch ' . In this way we will be using something  called ' METHOD SPOOFING ' in which Laravel and other frameworks understand, to update an instance that includes a FILE.
            Storage::delete($product->image);           // Delete old image

            $product->image = $request->image->store('');
        }

        // Once this is done we can update the image, but we will do this in a future section. We have done it now as per the above ' if ($request->hasFile('image')) { ' CODE.

        if ($product->isClean()) {
            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        $product->save();

        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->checkSeller($seller, $product);

        $product->delete();

        Storage::delete($product->image);                   // The Storage Facade ' Storage:: ' will allow us to easily manage files. Specifically in this case we want to delete a file. The delete() method ' ->delete() ' receives the name of the file relatively from the root folder of the image system that we have. It means from the public ' img ' folder. We just need to specify the name of the file, that is basically the value of the ' image ' attribute of the product ie: ' ->delete($product->image) ' . After this the image shall be removed, and then we can remove the instance. Of course we can do in different order, and in this case is basically the same. Maybe you are wondering about that when we using the delete method for the product we are using SoftDeletes, that means the product still existing in the database, so we should not remove permanently the image and you are completely right. But for now, we are just gonna see how to remove definitely the image, and in a future section we are going to see how to differentiate between permanent removal and soft deleting of a product in this case.

        return $this->showOne($product);
    }

    protected function checkSeller(Seller $seller, Product $product)
    {
        if ($seller->id != $product->seller_id) {
            throw new HttpException(422, 'The specified seller is not the actual seller of the product');
        }
    }
}
