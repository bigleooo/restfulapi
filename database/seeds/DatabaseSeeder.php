<?php

use App\Category;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');       // Remember that our tables have foreign keys between them, so we need to disable temporarily only for the database seeder the foreign key checks. So we need to execute a database statement directly. With this, the database seeder aren't gonna verity the foreign keys at the moment of truncate.

        User::truncate();                    // Every time we use database seeder, we need to clear all the database tables. We can use the model's truncate method to achieve this..
        Category::truncate();
        Product::truncate();
        Transaction::truncate();
        DB::table('category_product')->truncate();      // The Pivot Table doesn't have a model, so to clear this table, we need to use the ' DB ' Facade ' .

        User::flushEventListeners();                          // We use this because as per our code in AppServiceProvider boot(), we have an event listener that each time a user is created, an email is sent to that user to verify his email. Using this seeder thousands of user's will be created meaning thousands of emails will have to be sent. If using a third party mail service provider, this could be very expensive! so this code disables the User Model's Events.
        Category::flushEventListeners();
        Product::flushEventListeners();
        Transaction::flushEventListeners();

        $usersQuantity = 1000;
        $categoriesQuantity = 30;
        $productsQuantity = 1000;
        $transactionsQuantity = 1000;

        factory(User::class, $usersQuantity)->create();        // We can use the factory helper ' factory() ' specifying the class model to use and the quantity.
        factory(Category::class, $categoriesQuantity)->create();

        factory(Product::class, $productsQuantity)->create()->each(                // Now the product factory has a little detail because for every product that we create, we need to associate the category for this. We are going to do that randomly.  We are going to assign randomly from 5 possible categories to our product. For this we need to execute a function for each specific product that we are creating in that factory.
            function ($product) {                                                          // This function is gonna receive every specific product and we need to do some stuff with every one of them. First we gonna obtain randomly from 1-5 categories from the list of categories. So we need randomly again from 1-5 categories, it means we have random categories and random number of categories to assign for every product. Then we have a collection with several categories but we only need the ' ID ' for every of those categories. So for that we are gonna use the ' pluck ' method from the Laravel categories. After that we already have from 1-5 categories, and we just need to attach that categories for every specific product. The ' attach ' method receives an array with all the categories, it means with all the id's of the categories that we are gonna associate with that specific product.
                $categories = Category::all()->random(mt_rand(1, 5))->pluck('id');

                $product->categories()->attach($categories);                // The ' attach '  method is receiving an array of 5 categories ID, in which it will associate with specific product in the pivot table.
            }
        );

        factory(Transaction::class, $transactionsQuantity)->create();
    }
}
