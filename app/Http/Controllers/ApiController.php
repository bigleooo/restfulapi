<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use ApiResponser;                   // Now we can use every method in the ApiResponser Trait directly in our controllers, since they are all extending this controller ' ApiController ' , and this controller uses the ' ApiResponser ' Trait.

    public function __construct()
    {
        // Going to modify along the classes.
    }
}
