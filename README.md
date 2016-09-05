Rememberable, Laravel 5 query cache
===================================

[![Build Status](https://travis-ci.org/ameliaikeda/rememberable.svg?branch=master)](https://travis-ci.org/ameliaikeda/rememberable)

Rememberable is an Eloquent trait for Laravel 5.2+ that adds a transparent query cache to your models.

It works by simply remembering the SQL query that was used and storing the result.
If the same query is attempted while the cache is persisted it will be retrieved from the store instead of hitting your database again.

## Requirements

At current, you're required to be using Redis as your cache implementation.

This is planned to be changed in future, and pull requests are welcomed.

PHP 5.6+ is supported.

**Note:** This package will cause your increment/decrement operations on models that use it to fire the `saved` event afterwards. For most people, this should be of no concern, but to disable it, set `protected static $interceptable = false`.

## Installation

Install using Composer, just as you would anything else.

    composer require amelia/rememberable

The easiest way to get started with Eloquent is to create an abstract `App\Model` which you can extend your application models from. In this base model you can import the rememberable trait which will extend the same caching functionality to any queries you build off your model.

    <?php
    
    namespace App;

    use Amelia\Rememberable\Rememberable;
    use Illuminate\Database\Eloquent\Model as Eloquent;

    abstract class Model extends Eloquent
    {
        use Rememberable;
        
        /**
         * Remember _all_ queries on this model
         *
         * @var bool
         */
        protected static $rememberable = true;
    }

Now, just ensure that your application models from this new `App\Model` instead of Eloquent.

Alternatively, you can simply apply the trait to each and every model you wish to use `remember()` on.

## Usage

All queries are remembered automatically.

On update or delete, any query which contains the altered model will be removed from the cache.

This means that in almost all cases you can just set your base model to remember queries and forget about it.


## Flushing

If you wish to flush the query cache manually, you can issue a `Model::flush()` command.

`flush()` takes cache tag arguments; the tags applied to every single query are the model's class name, the model's table name, and `'rememberable'`.

So, to flush every query (say, during deployment, if you dont flush the entire cache), issue `::flush('rememberable')` on any model in your application that uses rememberable.

## Manual usage

Just like in [`watson/rememberable`](https://github.com/dwightwatson/rememberable), you can use `remember(int $minutes)` on specific queries if you dont have `$rememberable` set to `true` on the model in use.

    // Remember the number of users for an hour.
    // remembers "select count(*) from users"
    $users = User::remember(60)->count();

## Credits

This package is under the [MIT License](/LICENSE.txt).

It's a direct fork of `watson/rememberable`, with many added features.
