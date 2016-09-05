<?php

class Group extends RememberableStub
{
    protected static $rememberable = true;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
