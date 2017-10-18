<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract {

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)                             // The transform method can receive for example an array with the original values that we are going to transform or more interestingly receives an INSTANCE of the Object or the Model that we are going to transform. So in this case it is going to receive a ' User Instance ' . Then the transform method is going to return an array with the values of every attribute of our model. For example we have an ' id ' that is equal to ' => $user->id ' . But this is quite powerful because we can say that for example this ' id' is not an integer, it is a string ' (string) ' . But for now we are going to keep this as an integer ' (int) '. Another interesting and powerful feature for this transformation is that we can MODIFY the name of the INDEX e.g. index ' id ' . The index doesn't need to be named ' id ' . This can be for example ' identifier ' ie: " 'identifier' => (int) $user->id " . And the value of the ' identifier ' key is going to be the original value of the id of the user ie: $user->id .
    {
        return [
            'identifier'   => (int) $user->id,
            'name'         => (string) $user->name,
            'email'        => (string) $user->email,
            'isVerified'    => (int) $user->verified,
            'isAdmin'      => ($user->admin === 'true'),                     // Originally the admin value comes as a STRING from the database. " 'true' " or " 'false' " . And every time we are going to transform a string to a boolean, it is going to give us zero ' 0 ' or ' false ' . So we need to proceed in a little different way. We are going to return the value of this comparison. If the user admin is identical ' === ' to the string ' true ' , its going to return a boolean true. In other case it is going to return a boolean false. Please take into account that it is not returning a string, it is returning a boolean.
            'creationDate' => (string) $user->created_at,
            'lastChange'   => (string) $user->updated_at,
            'deletedDate'  => isset($user->deleted_at) ? (string) $user->deleted_at : null,        // The problem with the deleted_at attribute is that it can be null at any moment. So we need to return a string representation of the date or a null value. So that is going to depend if these values is set. If the deleted_at is set, we are going to return a string representation of this ie: ' (string)$user->deleted_at ' . In case of not we are going to return null ie: ' : null ' .
            'links' => [                // The links are ' hateoas hypermedia controls ' meaning are just links that make it easy for client to access other information from related models, or models that have relationship with in this example the User Model. But the User Model has none, so just itself.
                [
                    'rel' => 'self',
                    'href' => route('users.show', $user->id),              // This route need a user id to access information.
                ],
            ]
        ];
    }

    public static function originalAttribute($index)        // Going to receive the index as the transformed attribute and it's going to return the original attribute.
    {
        $attribute = [                                      // We're going to have a list of attributes and we are going to map every attribute to its original.
            'identifier' => 'id',
            'name' => 'name',
            'email' => 'email',
            'isVerified' => 'verified',
            'isAdmin' => 'admin',
            'creationDate' => 'created_at',
            'lastChange' => 'updated_at',
            'deletedDate' => 'deleted_at',
        ];

        return isset($attribute[$index]) ? $attribute[$index] : null;       // In this way we are removing the possibility of a property we are not transforming e.g. the ' password ' . And additionally we are going to know which attribute in the transformation used from the original model.
    }

    public static function transformedAttribute($index)        // Going to receive the index as the original attribute and it's going to return the transformed attribute.
    {
        $attribute = [                                      // We're going to have a list of attributes and we are going to map every original attribute to its transformed one.
            'id' => 'identifier',
            'name' => 'name',
            'email' => 'email',
            'verified' => 'isVerified',
            'admin' => 'isAdmin',
            'created_at' => 'creationDate',
            'updated_at' => 'lastChange',
            'deleted_at' => 'deletedDate',
        ];

        return isset($attribute[$index]) ? $attribute[$index] : null;       // In this way we are removing the possibility of a property we are not transforming e.g. the ' password ' . And additionally we are going to know which original attribute corresponds with which transformed attribute.
    }

}
