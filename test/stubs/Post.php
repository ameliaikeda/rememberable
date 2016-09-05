<?php

class Post extends RememberableStub
{
    protected static $rememberable = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, self::class, 'user_id', 'id');
    }
}
