<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{

    use ApiResponser;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {                                    // Validation exception thrown.
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof ModelNotFoundException) {                                 // Model exception is when we are trying to access an instance of a model e.g. Users, in which e.g. this user does not exist therefore this instance of the User Model does not exist.
            $modelName = strtolower(class_basename($exception->getModel()));        // The ' getModel() ' method gets the name of the instance of the model we are trying to access, which does not exist. We don't want show the namespace e.g. ' App\\User ' so we use ' class_basename() ' method, and we convert everything to lowercase e.g. User to user using the ' strtolower() ' method.

            return $this->errorResponse("Does not exists any {$modelName} with the specified identificator", 404);
        }

        if ($exception instanceof AuthenticationException) {                            // Not authenticated.
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->errorResponse($exception->getMessage(),403);          // 403 code mean unauthorized.
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('The Specified URL cannot be found', 404);      // When the Route endpoint does not exist e.g. restfulapi.dev/usersss
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('The specified method for the request is invalid', 405);        // When the method e.g. Post method does not exist in e.g. Buyers Controller.
        }

        if ($exception instanceof HttpException) {                           // This is a Generic Http Exception. If there is no specific if statement to specify the http exception, this will be handled over here.
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if ($exception instanceof QueryException) {
//            dd($exception);                             // ' dd() ' method is debug method, it allows us to see info related to this query exception. if we go to e.g. Postman and use delete method to delete user with id 1, we get the info related to this exception because we cannot delete the user due to foreign key constraints in either the products table if user is seller or transaction table if user is buyer. The info provided will contain an ' errorInfo ' attribute which is an array of 3, with index 1 being the query error code 1451 for foreign key constraints.
            $errorCode = $exception->errorInfo[1];

            if ($errorCode == 1451) {
                return $this->errorResponse('Cannot remove this resource permanently. It is related with another resource', 409);
            }
        }

        if (config('app.debug')) {             // Use the config helper to get access to the ' app ' file in the debug index. The app file is located in the Config folder/app.php . There is an INDEX option (it is a file that returns an ARRAY) called " 'debug' => env('APP_DEBUG', false) " that will let us know if the application is in debug mode or not (production mode or not), because if we are in production mode we need to know what the raw errors are which is rendered by the parent class render() method, and not get the json response for the unexpected exception.
            return parent::render($request, $exception);
        }

        return $this->errorResponse('Unexpected Exception. Try later', 500);                // You can get a lot of different kind of unexpected exceptions e.g. cannot establish connection to database when going to restfulapi/buyers using get method. Should work which would return you all the buyers, but sometimes something happen and will not work. This exception here is put at the very bottom of all our exception list, so that if the exception is not of the specific above exceptions, then this exception will take over and return this message for all different kind of unexpected exceptions.
                                                                                                           //The 500 code means that it is an exception from the server side.
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('Unauthenticated.', 401);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)           // We copied this from the parent class of the render method. What we will do is modify this method to return the errors as a Json Response independently if it is requesting Json or not.
    {
        $errors = $e->validator->errors()->getMessages();

        return $this->errorResponse($errors, 422);              // Here we are using out Trait, the ' ApiResponser ' Trait to return a json response.
    }
}

