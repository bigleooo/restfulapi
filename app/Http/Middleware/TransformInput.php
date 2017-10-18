<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $transformer)
    {
        $transformedInput = [];         // Transform the inputs into a new Array. We are going to fill input by input with the original names.

        foreach ($request->request->all() as $input => $value) {          // We need to be careful to only obtain the ' inputs ' and not the ' query strings ' in the URL, so we need to go deeper in the $request to obtain only that. And that can be done in this way $request->request, we are using the attribute request, and then just obtain all the fields ' ->all() ' . It is trying to obtain only the elements on the body of the request. It is going to be called $input and we are just going to keep it as $value. So we just need to create the new index for the transformed input. Now we can use the transformer to get the original attribute e.g. ' title ' will be switched to ' name ' in the case of a category instance. I mean if the user sends ' title ' it will know that the original attribute for title is ' name ' in the case of a category instance.
            $transformedInput[$transformer::originalAttribute($input)] = $value;
        }

        $request->replace($transformedInput);           // We need to replace the original inputs of the $request with the new one. For this we can use the ' replace() ' method sending only the transformed inputs. And then we can continue normally with our middleware.

        // From above, we were replacing the transformed inputs that the user used to its original named inputs ( i.e. in the case of categories, ' title ' to ' name ' ) so that the controller understands them.

        // From below we are obtaining the response from the controller and modifying the response to use the transformed property names and modified values from our transformer in the case of a Validation error message.

        $response = $next($request);        // Obtain the response before returning it, and modify it accordingly. We have a lot of responses possible, we have success response and error response. But there are again different kind of error responses, and at this point we are going to act only over the error responses. So we just need to be sure first that this response is an error response and that can be done if we know if there is an exception in the response.

        if (isset($response->exception) && $response->exception instanceof ValidationException) {       // If the exception attribute is set, so we know that it is an error. And we need to know if this error is a validation exception.
            $data = $response->getData();       // Obtain the data from the response. There are a lot of things that we can obtain from the response, but we only need the data. It is to note that when we receive an error response(in Json) we get a ' error ' and a ' code ' . What we need directly is the content of ' error ' that is an array with every attribute that we need to modify e.g. in the case if category we have ' name ' and ' description ' attribute. So we just need to go directly to the error element and transform this.

            $transformedErrors = [];

            foreach ($data->error as $field => $error) {
                $transformedField = $transformer::transformedAttribute($field);            // Lets obtain the name of the transformed field using the transformer using of course the transformed attribute method that we implemented in the corresponding transformer class. So again if the user has an error for the ' name ' , we are going to transform this to ' title ' in the case of ' Category ' for example, so we know its ' title ' using the transformer.

                $transformedErrors[$transformedField] = str_replace($field, $transformedField, $error);                // So once we have this, we just need to build the new transformed error. But remember the new error field VALUE still uses the same name of the Non-Transformed field name i.e. ' "name(NOW ITS title)": ["The name field is required."], ' . We need to replace every occurrences of these values there. For this we are going to use ' str_replace() ' method. In str_replace we need to send the original value ' $field ' and what we want to replace to ' $transformedField ' and of course where we are going to replace that ' $error ' . After that we have now completely transformed the errors.
            }

            $data->error = $transformedErrors;             // Data error attribute is going to be equal to the new transformed errors.

            $response->setData($data);           // Need to specify that new data to the response.
        }

        return $response;
    }
}
