<?php

class User extends RememberableStub
{
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
