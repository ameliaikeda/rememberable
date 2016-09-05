<?php


class BasicRememberTest extends RememberTestCase
{
    public function testBasicCaching()
    {
        User::create(['id' => static::ID, 'name' => 'Test']);

        $user = User::remember(10)->find(static::ID);

        static::$sql = false;

        $cachedUser = User::remember(10)->find(static::ID);

        $this->assertEquals($user, $cachedUser);
    }
}
