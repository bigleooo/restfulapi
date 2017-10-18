<?php

namespace App\Http\Controllers\Category;

use App\Category;
use App\Http\Controllers\ApiController;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function __construct()
    {
        parent::__construct();          // As we are using in some of our controllers the parent constructor, we need to create a constructor in ' ApiController ' that is the Parent Class, in order to avoid any kind of error.

        $this->middleware('transform.input:' . CategoryTransformer::class)->only(['store','update']);        // Register the middleware. This middleware is going to receive a new parameter i.e. the name of the transformer or basically the name of the class for the transformer. In the case we are using instances of category, we are going to use the Category Transformer and it is going to be executed only for the store() and update() methods found in this controller.
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();

        return $this->showAll($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];

        $this->validate($request, $rules);

        $newCategory = Category::create($request->all());

        return $this->showOne($newCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return $this->showOne($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $category->fill($request->intersect([               // First thing is to fill the category instance with he new values from the request. And for this we gonna use a method ' fill() ' . The fill method receives an array with the name and description we need. So for that we are gonna use the intersect method for the request to be absolutely sure that we are only sending the name and the description and not any other field that the client sends.
            'name',
            'description',                                  // Note if we only specified a name in the request, only the name will be filled.
        ]));

        if ($category->isClean()) {
            return $this->errorResponse('You need to specify any different value to update', 422);
        }

        $category->save();

        return $this->showOne($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();    // Remember that is not permanently removing the instance, it is just changing the ' deleted_at ' attribute in order to be hidden automatically by Laravel. This is because we are using the ' SoftDeletes '  Trait in the Category model, along with ' $protected dates = ['deleted_at']; ' , and also modified the ' categories ' table migration to include ' $table->softDeletes() ' which will add the column name ' deleted_at ' inside our categories table in our database. We cannot permanently delete the instance because we are using this instance ' ID ' as Foreign Keys in other tables.

        return $this->showOne($category);
    }
}
