<?php

namespace App;


use App\Scopes\BuyerScope;
use App\Transformers\BuyerTransformer;

class Buyer extends User
{
    public $transformer = BuyerTransformer::class;               // We are going to obtain the full namespace of the transformer, so remember to use the class operator ' ::class '.

    protected static function boot()            // Now to add the global scope ' BuyerScope '  we just need to redefine the boot method in this buyer model. The boot() method is basically executed when an instance of this model is created. First we need to call the boot() method of the parent class ' parent::boot(); ' . It is important not to modify the regular behaviour of Laravel, and than call the ' addGolbalScope() ' method sending an instance of our ' BuyerScope ' . Import the definition and we shall be done now.
    {
        parent::boot();

        static::addGlobalScope(new BuyerScope);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
