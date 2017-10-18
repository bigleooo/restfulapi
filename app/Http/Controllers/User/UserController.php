<?php

namespace App\Http\Controllers\User;

use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public function __construct()
    {
        parent::__construct();          // As we are using in some of our controllers the parent constructor, we need to create a constructor in ' ApiController ' that is the Parent Class, in order to avoid any kind of error.

        $this->middleware('transform.input:' . UserTransformer::class)->only(['store', 'update']);        // Register the middleware. This middleware is going to receive a new parameter i.e. the name of the transformer or basically the name of the class for the transformer. In the case of user, we are going to use the User Transformer and it is going to be executed only for the store() and update() methods found in this controller.
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return $this->showAll($users);
//        return $users;          // We can use this too, but we don't have the possibility to modify a little the structure e.g. change data to users, and specify the response code. In this case the 200 response means its an ok response meaning everything is working correctly.
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)         // Validate the request that we automatically receive via method dependency injection.
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',       // Email is required, valid email format and is unique in the users table.
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $rules);                  // If the validation fails, Laravel is gonna throw an exception. We will handle this exception in a future lesson. But after this line we are sure that we got good data.

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);                        // The ' create() ' method receives an array with all the specific fields for that user.

        return $this->showOne($user, 201);         // This time return a 201 response meaning that that has been created.
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)            // From laravel 5.2 onwards Laravel introduced implicit Model binding which will allow us to automatically resolve an instance of the model based on the ID that we will receive. For this case we use parameters ' User ' as the model, and $user as the instance of the model which is based upon the ID that was received in the URL.
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'email' => 'email|unique:users,email,' . $user->id,             // Email must be unique, but the person updating his data could again be sending his previous email, so we need to except from this validation the user performing this request. So for that we need to validate the email except and excepting the user with the received id.
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,      // Additionally we have an admin field. A user can be modified in order to change his account to be an admin or not. So the admin values must be ' in: ' 2 possible values. Is an admin user or it is not. We don't need to validate anything related to the verification or the verified field because that field cannot be directly modified. The only way to modify that value is of course is verifying the email, but cannot be changed directly.
        ];

        $this->validate($request, $rules);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')) {
            if (!$user->isVerified()) {
                return $this->errorResponse('Only verified users can modify the admin field', 409);         // A description of the error,  a status code that in this case will be 409 specifying a conflict, and the response code 409.
            }

            $user->admin = $request->admin;             // Of course this operation must be done by an admin user, but currently we don't have any way to verify that. Of course in the future sections in the security layer we are going to see how to verify that some specific operations are performed only by an administrator user.
        }

        if (!$user->isDirty()) {                        // If the ' isDirty() '  method returned true, it means the user changed.
            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        $user->save();                                                // Now if anything changed, than we just need to save those changes. That can be done using the ' save() ' method.

        return $this->showOne($user);

                                                                      // Note when we test in postman using a PUT/PATCH/DELETE method we cannot send data in Body using ' form-data ' as we were doing for GET & POST methods , we need to use ' x-www-form-urlencoded ' . This will add a header ' Content-Type = application/x-www-form-urlencoded ' .
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();           // firstOrFail() method because if he doesn't exist, Laravel will return a modelnotfound exception.

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;

        $user->save();

        return $this->showMessage('The account has been verified successfully');
    }

    public function resend(User $user)
    {
        if ($user->isVerified()) {
            return $this->errorResponse('This user is already verified', 409);
        }

        retry(5, function () use ($user) {
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('The verification email has been resent');
    }
}
