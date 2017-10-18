<?php

namespace App;

use App\Transformers\UserTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {

    use Notifiable, SoftDeletes;                    // The SoftDeletes Trait is built in Laravel and must be imported. Now we can delete a User e.g with id ' 1 ' , and it will allow us, because the model instance still exist in the database, except that now the ' deleted_at ' column contains the date in which we soft deleted this user. We will not get any foreign key constraints because the user is still there, except that when we look for the user, LARAVEL will not show because the ' deleted_at ' column for the user has a value of the date in which we soft deleted the user/instance of the model.

    const VERIFIED_USER = '1';
    const UNVERIFIED_USER = '0';

    const ADMIN_USER = 'true';     // Note this is a string, therefore in our migration table for creating users, The users table property ' admin ' must also be a String.
    const REGULAR_USER = 'false';

    public $transformer = UserTransformer::class;               // We are going to obtain the full namespace of the transformer, so remember to use the class operator ' ::class '.
    protected $table = 'users';     // Because when we use ' php artisan db:seed ' command, it will tell us that the sellers or buyers table does not exist. This is because the Seller and Buyer Model is extending the Users Model, and therefore they don't have there own table. So we need to use this here to fix that.
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verified',
        'verification_token',
        'admin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    public function setNameAttribute($name)                 // A Mutator. Automatically Laravel is gonna call the setNameAttribute() function every time we are establishing a value for the name attribute.
    {
        $this->attributes['name'] = strtolower($name);
    }

    public function getNameAttribute($name)                 // An Accessor.
    {
        return ucwords($name);                              // Transform the name attribute from the database to make every word Capital Letters.
    }

    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }

    public function isVerified()
    {
        return $this->verified == User::VERIFIED_USER;          // Note do not need to import User class ( User:: ), as we are currently in the User Class. Also we do not need to import other models when using Facades to access models because all these models are in the same namespace.
    }

    public function isAdmin()
    {
        return $this->admin == User::ADMIN_USER;                // Basically we are just accessing the const ' ADMIN_USER ' from an instantiation of the User class, which all instantiations hold this constant. I believe could have just used ' $this ' instance of the user model to access this constant as all instances have this constant, but the teacher used this way.
    }

    public static function generateVerificationCode()
    {
        return str_random(40);
    }

    // You might be wondering what about the relationships for the ' User Model ' . There are no relationships directly. The relationship is implemented INDIRECTLY
    //using the ' Buyer ' and ' Seller ' Models as these models are extending from the ' User ' Model, so inherits everything from the User Model. So in a way
    // A user being a seller has products and A user being a buyer has transactions.
}
