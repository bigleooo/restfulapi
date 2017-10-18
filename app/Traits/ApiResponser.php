<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

trait ApiResponser                                              // A trait is basically a method for Code Re-USE. Basically this code is applicable along multiple classes. All our api controllers will be returning a response in json with data and a code attached, so why not use a trait so that we don't repeat any code. Our Api controllers will extend the ' ApiController ' Controller class which uses this trait, which allows all controllers to have this trait which includes all the below methods!
{
    private function successResponse($data, $code)              // This function receives the data to be returned and the code of that response.
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    protected function showAll(Collection $collection, $code = 200)         // The function returns a success response with the data element, the collection and the status code.
    {
        if ($collection->isEmpty()) {                                       // In the case of an empty collection we still need to include the 'data' element inside the first parameter array since the $collection data is not being transformed since it is empty.
            return $this->successResponse(['data' => $collection], $code);
        }

        $transformer = $collection->first()->transformer;                   // Remember we included a transformer property in all our models that is linked to their individual Transformers ie: in the User Model we have a property ' $transformer = UserTransformer::class ' providing the full namespace of the Transformer. So we just need to get an instance of that e.g. User Model using the ' first() ' method inside that Collection and get that transformer property. The problem is that we are using the first element ->first() , so what happens if that collection is empty, in that case we need to call directly the ' successResponse() ' method. If the collection is empty, we just need to return a success response with an empty collection.

        $collection = $this->filterData($collection, $transformer);             // This will filter our collection based on the query parameter(s) and its value(s) we put in our link e.g. ' restfulapi.dev/users?isVerified=0&isAdmin=true&sort_by=name ' , we put the query parameter ' isVerified ' and its value is ' 0 ' , so our collection will be filtered to only hold the data(instances) of users that has not been verified represented by 0, also the collection will be further filtered to hold all data(instances) that are admins represented by true and again further sorted by the names of the instances.. Use the ' sortData() ' method before the ' transformData() ' method as the transformData() method is an instance of fractal, not collection instance, therefore the sortData() method will not work if used after the transformData() method.
        $collection = $this->sortData($collection, $transformer);             // This will sort our collection based on the value of the parameter we put in our link e.g. ' restfulapi.dev/users?sort_by=name ' , we put the parameter ' sort_by ' and its value is ' name ' , so our collection will be sorted by name. Use the ' sortData() ' method before the ' transformData() ' method as the transformData() method is an instance of fractal, not collection instance, therefore the sortData() method will not work if used after the transformData() method.
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);      // This is an Array now, not an instance of Collection.
        $collection = $this->cacheResponse($collection);                    // The cache system is going to allow us to save in a file or whatever system that we choose the status or the current status of the database for a specific lapse of time. It is going to allow us to reduce the job of our database.

        return $this->successResponse($collection, $code);      // Fractal is automatically including the data element for all its transformation so we do not need to include it in our first parameter ' ['data' => $collection] ' , it can just be ' [$collection] ' .
    }

    protected function showOne(Model $instance, $code = 200)         // Instead of a collection the showOne() is gonna receive a model and return that instance.
    {
        $transformer = $instance->transformer;

        $instance = $this->transformData($instance, $transformer);

        return $this->successResponse($instance, $code);    // Fractal is automatically including the data element for all its transformation so we do not need to include it in our first parameter ' ['data' => $instance] ' , it can just be ' [$instance] ' .
    }

    protected function showMessage($message, $code = 200)
    {
        return $this->successResponse(['data' => $message], $code);
    }

    protected function filterData(Collection $collection, $transformer) {   // Filters the data based on the query parameter and its value that we enter.
        foreach (request()->query() as $query => $value) {                  // e.g. In postman we used ' restfulapi.dev/users?isVerified=0&isAdmin=true&sort_by=name ' , in this case $query will be ' isVerified ' , whilst $value will be ' 0 ' , additionally $query will be ' isAdmin ' whilst $value will be ' true ' . Note that the last query parameter ' sort_by ' will be passed as it will not exist when we pass it through the related transformers ' originalAttribute() '  function, but it will be used in the ' sortData() ' method in this trait to sort the already filtered collection(by isVerified and isAdmin) by ' name ' .
            $attribute = $transformer::originalAttribute($query);

            if (isset($attribute, $value)) {
                $collection = $collection->where($attribute, $value);
            }
        }
        return $collection;             // Of course if we don't have any query parameters, we will return the collection without any filtering.
    }

    protected function sortData(Collection $collection, $transformer)
    {
        if (request()->has('sort_by')) {                        // e.g. in postman we use ' restfulapi.dev/users?sort_by=name ' .
            $attribute = $transformer::originalAttribute(request('sort_by'));       // Passes the ' sort_by ' value to the ' originalAttribute() ' function inside the relevant transformer. This will allow us to use the property names that are gonna be used when the data is transformed e.g. ' identifier ' instead of ' id ' , so we can sort by ' identifier ' .

            $collection = $collection->sortBy->{$attribute};        // Of course if this attribute does not exist inside the collection, it is not going to fail, it is just going to return something lke a random sort collection. We don't need to take care about that because we know that it is not going to fail at all. So now after laravel 5.4. was introduced a new feature called ' high-order-messages ' that basically allow us to use some methods from the collection as attributes instead of just methods. So if we want to use the high order messages from the ' sortBy() ' method in this case we just need to do it in this way i.e. from ' $collection = $collection->sortBy($attribute);  ' to ' $collection = $collection->sortBy->{$attribute}; ' Of course since it is a variable, we should use ' curly braces ' in order not to have any problems with the syntax(s). After this we should be able to see what's happening. We can use it in the ' showAll() ' method because the showAll() method is used for Collections..
        }
        return $collection;
    }

    protected function paginate(Collection $collection)
    {
        $rules = [                                                  // Not more than 50 or less than 2 elements per page.
            'per_page' => 'integer|min:2|max:50',
        ];

        Validator::validate(request()->all(), $rules);

        $page = LengthAwarePaginator::resolveCurrentPage();         // That is the value of the ' page ' query parameter that we currently have in the URL. Fortunately for us the ' LengthAwarePaginator ' has a method for this. The ' LengthAwarePaginator ' Class allow us to use one of its methods ' resolveCurrentPage() ' method to find the current page. After this we know which is the current page we are on.

        $perPage = 15;                                              // Number of elements per page.
        if (request()->has('per_page')) {                       // Changes the ' $perPage ' number of elements allowed based on if the user entered the URL to include the ' per_page ' query parameter e.g. ' http://restfulapi.dev/users?per_page=3 ' , in this example case specifying that 3 elements should be displayed on the page.
            $perPage = (int) request()->per_page;
        }

        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();      // Divide the collection using the slice() method based on the number of elements we allow to be displayed per page. The slice() method receives from which element we are going to slice, and the quantity of elements after this we are going to use. So it depends on the page and the quantity of elements per page. But remember that a collection starts from ' 0 ' like any regular array, so we need to obtain the page minus ( - ) ' 1 ' , and then multiply by the quantity of elements per page. In this way if we are in the page number 1, it is going to give zero and we are going to start from the ZERO ELEMENT. And we are going to obtain from the ' 0 ' to the quantity of elements per page($perPage), in this case is 15. After this we just obtain the values and we have now the results.

        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [       // Create paginator instance. We are going to create an instance of the ' LengthAwarePaginator ' that receives the results, the real size of the collection, the quantity of elements per page, the current page, and finally some options. There are several options, but we only need to specify one of them the ' path ' . The path is going to allow us to resolve the next, or previous page depending of the current status. Fortunately for us again, the ' LengthAwarePaginator ' class provides us with a method that does this called ' resolveCurrentPath() ' . After this we have the paginated version, in which we just need to return.
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());          // The problem here is when we resolve the ' path ' , it is only taken into account the (query) parameter called ' page ' i.e. the parameter that says the current page of the URL. It is going to ignore the other query parameters. The ' appends() ' methods sends all the parameters of the request. Automatically Laravel is going to ignore the page parameter. it will appends all query parameters which exist e.g. sort_by, isVerifies, isAdmin etc..

        return $paginated;
    }

    protected function transformData($data, $transformer)         // Parameters are $data to be transformed, and the $transformer to be used. Note that data can be an instance of any model or a collection of instances of any model.
    {
        $transformation = fractal($data, new $transformer);       // Basically at this moment the $transformer variable is an instance of the fractal transformer and it is not very useful for us right now, so what we are going to do, is transform to an array. After this we have the data completely transformed, so what we need to do is just use it in the showAll() and showOne() methods. The variables $collection and $model in those methods are instances of the different models depending on which one is using it. So we designed our API very well, so we can use the transformations in those methods as well as they are also instances of those specific models. Note here we can use the fractal helper ' fractal() ' because we included the fractal package service provider in our config/app.php file under providers ie: ' Spatie\Fractal\FractalServiceProvider::class, ' .

        return $transformation->toArray();
    }

    protected function cacheResponse($data)        // It is now receiving the DATA not a collection, because as you know the transformed collection is now an ARRAY and not a collection.
    {
        $url = request()->url();                    // Does not keep query parameters. So regardless of what query parameters that was put in the url, the cache system is going to cache that data to the url as if there was no query parameters, therefore regardless of what query parameters we input again with the same url, it still going to return the same data. To avoid this, we do the below.
        $queryParams = request()->query();          // Gets all the query parameters.

        ksort($queryParams);        // Sort the query parameters based on the key of the array. So for this we need to use the ' ksort() ' method (keysort). the ksort acts as reference and not by value, so we just don't need to assign, we just need to go ahead with this knowing that the query params are now sorted.

        $queryString = http_build_query($queryParams);        // Now need to build a new query string based on these sorted query parameters. The $queryString is going to be equal to the query parameters in a string representation. So for this we need to use a ' http_build_query() ' method or function from php sending the query parameters Array.

        $fullUrl = "{$url}?{$queryString}";        // After this we are going to build a full URL that is going to be equal to the original url and the query string that we recently built. Now we are going to be sure that the cache system is going to change or recreate a new cache version of the request, based on different query parameters.

        return Cache::remember($fullUrl, 30/60, function() use ($data) {       // Basically the remember() method for the Cache Class will allow us to remember the URL which we used e.g. ' http://restfulapi.dev/users ' and its DATA it provided, it will store it for 30 seconds, and the third parameter will be a Closure that we can use to return the data. So if we got a list of users returned to us from a URL e.g. user1, user2, user3 and we deleted user1, we would still receive user1 as part of the data from the URL for 30 seconds, as this data was cached(stored) for 30 seconds.
            return $data;
        });
    }
}
