<?php

namespace App\Providers;

use App\Mail\userCreated;
use App\Mail\UserMailChanged;
use App\Product;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        User::created(function ($user) {
            retry(5, function () use ($user) {                                  // The retry helper receives 3 parameters. The first is quantity of times that we are going to retry this action. The second parameter is going to be a function that will be executed and retried several times. Finally the last parameter is going to be the quantity of milliseconds we need to wait before trying again. The ' to($user) ' method Laravel automatically understands that we will send the email to ' to($user->email) ' .
                Mail::to($user)->send(new UserCreated($user));
            }, 100);
        });

        User::updated(function ($user) {
            if ($user->isDirty('email')) {                                              // The isDirty() method will check if the email attribute is dirty meaning has changed.
                retry(5, function () use ($user) {
                    Mail::to($user)->send(new UserMailChanged($user));                  // to($user) Laravel automatically understands that we will send the email to ' to($user->email) ' .
                }, 100);
            }
        });
                                                                                // After Laravel 5.4 we have several ways to deal with the event stuff. We will use the old approach. This way is better since Laravel already gives us predefined events for the models and we can use it directly in this file, the AppServiceProvider.
        Product::updated(function ($product) {                                   // Listen to the updated event for the product model. This function is basically gonna receive a closure which is basically another function. This function is gonna receive the instance of the product that we updated. So after the updated process, we need to be sure if the product quantity is 0 && its Status is ' Available ' yet. If it is so, then we need to change the product status to ' Unavailable ' , then save.
            if ($product->quantity == 0 && $product->isAvailable()) {
                $product->status = Product::UNAVAILABLE_PRODUCT;

                $product->save();
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
