<?php

class BasicUser extends RememberableStub
{
    protected static $rememberable = false;
    protected $table = 'users';
}
