<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [                                                           // The middleware's that are included in this protected $middleware attribute are going to be executed automatically in EVERY REQUEST. It means that those are global middleware's.
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,                                        // To Remove the empty spaces from the beginning or the end of a string.
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,        // If we receive an empty field in the request, it is going to be transformed automatically to null, in order to make our validations a little easy.
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [                                                     // Middleware that is executed in the routes web.php file. The WEB routes. How can we know that? Well if we go directly to our providers directory/RouteServiceProvider, it will use this middleware in the mapWebRoutes() & the API Routes will use its own middleware in the mapApiRoutes().
        'web' => [
            'signature:X-Application-Name',
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [                                                                      // Middleware that is executed in the routes api.php file. The API routes. As you can see in the API group middleware, we can use named middleware's. We can also use them in the WEB group middleware's. Unfortunately cannot use named middleware's for the Global Middleware's.
            'signature:X-Application-Name',                                                                // We can add this signature middleware at the beginning of the group since it is an after middleware. Also if any others of those middleware build a response, it is going to automatically add the ' header ' independently on the kind of response. So it is a good idea in this case to add this middleware at the first position in this list. In that way this middleware is gonna leave the other middleware's to act, perform any kind of modification and to be executed. Because for example if we add this after the ' throttle ' middleware, if the throttle middleware fails in some way, we are not going to add the signature and that is not what we want. We really want to add the signature in all possible responses as we can, so again its a really good idea to use it in the first position. Now we just need to send one parameter to this middleware, and we do this with a semi column and the parameter name ie: ' :{parameter name} ' .
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [                                                      // The ' routeMiddleware ' are basically the named middleware's. Those are middleware's with a name. e.g. The middleware name ' auth ' and its class definition for that middleware. We can have several middleware's in our project and in fact we can create our own middleware's and register them with a name. A very important stuff with a named middleware are that middleware's can receive parameters. You can see for the API middleware group above, the 'throttle:60,1' middleware receives parameters. Note that only named middleware's can receive attributes, the others cannot receive attributes directly from the name. The NAMED middleware's can be used in different cases, we can use it directly in ' $middlewareGroups ' normally, but we can use it directly in our ROUTES or even better, we can use it directly in our CONTROLLERS. We can go in the controller's CONSTRUCTOR and specify an attribute called middleware and say ok i want to use this middleware with this specific name and even sending some attributes specifically for all methods of this controller or for example any specific kind of method ie: only one of them or all of them except others.
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \App\Http\Middleware\CustomThrottleRequests::class,
        'signature' => \App\Http\Middleware\SignatureMiddleware::class,
        'transform.input' => \App\Http\Middleware\TransformInput::class,
    ];
}
