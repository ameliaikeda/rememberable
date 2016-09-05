<?php

use Amelia\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;

class RememberableStub extends Model
{
    protected static $unguarded = true;
    protected static $rememberable = true;
    public $incrementing = false;

    use Rememberable;
}
