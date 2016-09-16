<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

abstract class RememberTestCase extends PHPUnit_Framework_TestCase
{
    const ID = '00000000-0000-0000-0000-000000000000';
    const USER_KEY = 'rememberable:37e1e4277521620256c524b5598dec2e19a04bfaf581bb9da4f83c3ad4ff7dbb7b03e7db5175eb2f3e507b4f8133fc2837eb392f7a92ce118ebff3a0e72cad9a';

    /**
     * Is SQL allowed to be issued?
     *
     * @var bool
     */
    protected static $sql = true;

    public static function setUpBeforeClass()
    {
        DB::listen(function ($query) {
            if (static::$sql === false) {
                throw new SqlIssuedException($query->sql."\n".json_encode($query->bindings, JSON_PRETTY_PRINT));
            }
        });
    }

    public function setUp()
    {
        Cache::flush();
        DB::beginTransaction();
    }

    public function tearDown()
    {
        Cache::flush();
        DB::rollBack();

        static::$sql = true;
    }
}
