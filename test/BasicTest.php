<?php

class BasicRememberTest extends RememberTestCase
{
    public function testBasicCaching()
    {
        BasicUser::create(['id' => static::ID, 'name' => 'Test']);

        $user = BasicUser::remember(10)->find(static::ID);

        static::$sql = false;

        $cachedUser = BasicUser::remember(10)->find(static::ID);

        $this->assertEquals($user, $cachedUser);
    }
}
