<?php

namespace App;


use App\Scopes\SellerScope;
use App\Transformers\SellerTransformer;

class Seller extends User
{
    public $transformer = SellerTransformer::class;               // We are going to obtain the full namespace of the transformer, so remember to use the class operator ' ::class '.

    protected static function boot()        // Laravel will call this boot method automatically when creating an instance of this Seller Model.
    {
        parent::boot();

        static::addGlobalScope(new SellerScope);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
