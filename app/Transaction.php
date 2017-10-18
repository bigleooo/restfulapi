<?php

namespace App;

use App\Transformers\TransactionTransfomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    public $transformer = TransactionTransfomer::class;               // We are going to obtain the full namespace of the transformer, so remember to use the class operator ' ::class '.
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'quantity',
        'buyer_id',
        'product_id'
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);          //Buyer::class means ' Build a Buyer Class ' .
    }

    public function product()
    {
        return $this->belongsTo(Product::class);        // Facades provide a static interface to access a class. Basically laravel will do all the things such as instantiating a class, resolving all the dependencies out of the IOC container, things like that ect..., which would allow us to use the facade as an interface to access the classes methods ect. So now we can see that these facades, they're basically support classes that give us access to an Object in the IOC Container.
    }
}
