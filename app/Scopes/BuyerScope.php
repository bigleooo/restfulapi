<?php

namespace App\Scopes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BuyerScope implements Scope                   // In order to have a scope we need to implement an interface called ' Scope ' . This interface is provided by Laravel, and in order to use this interface, we need to implement a method called ' apply() ' .
{
    public function apply(Builder $builder, Model $model)           // apply() method receives an Eloquent Builder and a Model as well.
    {
       $builder->has('transactions');                      // Now what we gonna do is just modify the Builder adding a restriction in this case to ' has('transactions') ' . The next step is to say to our buyer model to use directly this global scope every time when it is building a query. And that can be done, calling the ' addGlobalScope() ' method directly inside the boot method of the buyer model.
    }
}
