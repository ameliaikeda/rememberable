<?php

class User extends RememberableStub
{
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
