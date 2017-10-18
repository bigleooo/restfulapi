<?php

namespace App\Http\Middleware;

use Closure;

class SignatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  String $headerName
     * @return mixed
     */
    public function handle($request, Closure $next, $headerName = 'X-name')
    {
        $response = $next($request);                            // We are acting after we get the response. The default here when we create a middleware is ' return $next($request) ' .

        $response->headers->set($headerName, config('app.name'));                              // This set() method is gonna receive as parameters, the name of the header and the value of this header. The name is gonna be a variable ' $headerName ' and the value the name of our application which we can obtain directly from our configuration file using the ' config() ' helper. As the header name is a variable, it means we shall receive an attribute or a parameter in our handle() method. So we just need to add this. We are gonna add a default name for this parameter. Once we added the header to the response, we just need to return the response ie: ' return $response ' . We have now completely created our middleware. The next step is to register the middleware in the kernel file.

        return $response;
    }
}
