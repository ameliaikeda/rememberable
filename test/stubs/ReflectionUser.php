<?php

class ReflectionUser extends RememberableStub
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     *
     * @relation
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @relation
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @relation
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
