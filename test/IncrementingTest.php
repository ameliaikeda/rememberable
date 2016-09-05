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
    }
}
