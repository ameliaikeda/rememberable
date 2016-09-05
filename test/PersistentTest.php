<?php

class PersistentTest extends RememberTestCase
{
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
