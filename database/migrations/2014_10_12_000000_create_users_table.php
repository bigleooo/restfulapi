<?php

use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->string('verified')->default(User::UNVERIFIED_USER);  // Here we need to import the User class, as we are not in the same namespace as the User Model class.
            $table->string('verification_token')->nullable();
            $table->string('admin')->default(User::REGULAR_USER);        // Must be string as the User Class (model) constant ' REGULAR_USER ' holds a string of ' false ' .
            $table->timestamps();
            $table->softDeletes();          // This will add a new field ' deleted_at ' .
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
