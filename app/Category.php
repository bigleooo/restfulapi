<?php

namespace App;

use App\Transformers\CategoryTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    public $transformer = CategoryTransformer::class;               // We are going to obtain the full namespace of the transformer, so remember to use the class operator ' ::class '.
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'description'
    ];
    protected $hidden = [
        'pivot'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);      // Many to Many Relationship
    }
}
