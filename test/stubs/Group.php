<?php

class Group extends RememberableStub
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
