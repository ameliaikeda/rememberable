<?php

class UninterceptableUser extends RememberableStub
{
    protected static $interceptable = false;
    protected $table = 'users';
}
