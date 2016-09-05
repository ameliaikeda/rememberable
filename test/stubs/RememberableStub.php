<?php

use Amelia\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;

class RememberableStub extends Model
{
    protected static $unguarded = true;
    public $incrementing = false;

    use Rememberable;
}
