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

    public function testPersistentCachingWhenDeleting()
    {
        Group::create(['id' => static::ID, 'name' => 'Delete Test']);

        $group = Group::find(static::ID);

        $group->delete();

        $cached = Group::find(static::ID);

        $this->assertNull($cached);
    }

    /**
     * @expectedException SqlIssuedException
     */
    public function testSqlIssuedAfterDeletion()
    {
        Group::create(['id' => static::ID, 'name' => 'Delete Test']);

        $group = Group::find(static::ID);

        $group->delete();

        static::$sql = false;

        Group::find(static::ID);
    }

    /**
     * @expectedException SqlIssuedException
     */
    public function testSqlIssuedAfterUpdating()
    {
        Group::create(['id' => static::ID, 'name' => 'Delete Test']);

        $group = Group::find(static::ID);

        $group->update(['name' => 'foobar']);

        static::$sql = false;

        Group::find(static::ID);
    }
}
