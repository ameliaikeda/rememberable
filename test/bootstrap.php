<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Redis\Database;
use Illuminate\Support\Facades\Facade;

$container = new Container();
Container::setInstance($container);

$container->bind('cache', function ($app) {
    return new CacheManager($app);
});

$container->bind('config', function () {
    return new Illuminate\Config\Repository([
        'database' => [
            'redis' => [
                'cluster' => false,
                'default' => [
                    'host' => 'localhost',
                    'password' => null,
                    'port' => 6379,
                    'database' => 0,
                ],
            ],
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'prefix' => '',
                ],
            ],
            'fetch' => PDO::FETCH_CLASS,
        ],
        'cache' => [
            'default' => 'redis',
            'stores' => [
                'redis' => [
                    'driver' => 'redis',
                    'connection' => 'default',
                ],
            ],
            'prefix' => 'remember-test',
        ],
    ]);
});

$container->singleton('redis', function ($app) {
    return new Database($app['config']['database.redis']);
});

$capsule = new Illuminate\Database\Capsule\Manager($container);

// set this instance as global for tests
$capsule->setAsGlobal();

$container->singleton('db', function ($app) use ($capsule) {
    return $capsule->getDatabaseManager();
});

$container->singleton('events', function ($app) {
    return (new Dispatcher($app));
});

$capsule->bootEloquent();

$builder = $capsule->getConnection()->getSchemaBuilder();

$builder->create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->timestamps();
});

$builder->create('group_user', function (Blueprint $table) {
    $table->uuid('group_id');
    $table->uuid('user_id');

    $table->primary(['user_id', 'group_id']);
});

$builder->create('groups', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->timestamps();
});

Facade::setFacadeApplication($container);
