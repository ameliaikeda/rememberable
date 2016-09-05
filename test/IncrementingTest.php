<?php

class IncrementingTest extends RememberTestCase
{
    public function testIncrementingModelsPopsCache()
    {
        $group = Group::create(['id' => static::ID, 'name' => 'counter test', 'counter' => 0]);

        $cached = Group::find(static::ID);

        $group->increment('counter');

        $new = Group::find(static::ID);

        $this->assertNotEquals($cached, $new);

        $this->assertEquals(1, $group->counter);
        $this->assertEquals(1, $new->counter);

        static::$sql = false;

        // check we've properly cached here.
        Group::find(static::ID);
    }

    public function testIncrementingModels_Uninterceptable()
    {
        $group = UninterceptableUser::create(['id' => static::ID, 'name' => 'counter test', 'counter' => 0]);

        $cached = UninterceptableUser::find(static::ID);

        $group->increment('counter');

        $new = UninterceptableUser::find(static::ID);

        $this->assertEquals($cached->counter, $new->counter);

        $this->assertEquals(1, $group->counter);
        $this->assertEquals(0, $new->counter);

        static::$sql = false;

        // check we've properly cached here.
        UninterceptableUser::find(static::ID);
    }

    public function testDecrementingModelsPopsCache()
    {
        $group = Group::create(['id' => static::ID, 'name' => 'counter test', 'counter' => 0]);

        $cached = Group::find(static::ID);

        $group->decrement('counter');

        $new = Group::find(static::ID);

        $this->assertNotEquals($cached, $new);

        $this->assertEquals(-1, $group->counter);
        $this->assertEquals(-1, $new->counter);

        static::$sql = false;

        // check we've properly cached here.
        Group::find(static::ID);
    }

    public function testDecrementingModels_Uninterceptable()
    {
        $group = UninterceptableUser::create(['id' => static::ID, 'name' => 'counter test', 'counter' => 0]);

        $cached = UninterceptableUser::find(static::ID);

        $group->decrement('counter');

        $new = UninterceptableUser::find(static::ID);

        $this->assertEquals($cached->counter, $new->counter);

        $this->assertEquals(-1, $group->counter);
        $this->assertEquals(0, $new->counter);

        static::$sql = false;

        // check we've properly cached here.
        UninterceptableUser::find(static::ID);
    }
}
