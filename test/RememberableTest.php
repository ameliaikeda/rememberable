<?php

use Amelia\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RememberableTest extends PHPUnit_Framework_TestCase
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
                throw new SqlIssuedException($query->sql);
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

    public function testBasicCaching()
    {
        User::create(['id' => static::ID, 'name' => 'Test']);

        $user = User::remember(10)->find(static::ID);

        static::$sql = false;

        $cachedUser = User::remember(10)->find(static::ID);

        $this->assertEquals($user, $cachedUser);
    }

    public function testPersistentCaching()
    {
        Group::create(['id' => static::ID, 'name' => 'Test Group']);

        $group = Group::find(static::ID);

        static::$sql = false;

        $cached = Group::find(static::ID);

        $this->assertEquals($group, $cached);
    }

    public function testPersistentCachingPopsCorrectlyWhenUpdating()
    {
        Group::create(['id' => static::ID, 'name' => 'Test Group']);

        $group = Group::find(static::ID);

        static::$sql = false;

        $this->assertEquals($group, Group::find(static::ID));

        static::$sql = true;

        $group->update(['name' => 'Test Group Two']);

        $group = Group::find(static::ID);

        static::$sql = false;

        $this->assertEquals($group, Group::find(static::ID));
    }
}

class RememberableStub extends Model
{
    protected static $unguarded = true;
    public $incrementing = false;

    use Rememberable;
}

class User extends RememberableStub
{
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}

class Group extends RememberableStub
{
    protected static $rememberable = true;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

class SqlIssuedException extends Exception
{
}
