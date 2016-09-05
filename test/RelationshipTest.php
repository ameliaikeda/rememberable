<?php

class RelationshipTest extends RememberTestCase
{
    public function testBelongsTo()
    {
        $user = User::create(['id' => static::ID, 'name' => 'test']);
        $post = Post::create(['id' => static::ID, 'text' => 'test post', 'user_id' => $user->id]);

        $user = $user->fresh();
        $post = $post->fresh();

        $this->assertEquals($user, $post->user);

        static::$sql = false;

        // should still be cached
        $user = $user->fresh();
        $post = $post->fresh();

        $this->assertEquals($user, $post->user);
    }

    public function testHasMany()
    {
        list($user, $post, $commenter) = $this->fixtures();

        $comments = $commenter->comments;

        $commenter = $commenter->fresh();

        static::$sql = false;

        $this->assertEquals($comments, $commenter->comments);
    }

    public function testHasManyThrough()
    {
        list($user, $post, $commenter) = $this->fixtures();

            $commenters = $post->users;

        $post = $post->fresh();

        static::$sql = false;

        $this->assertEquals($commenters, $post->users);
    }

    protected function fixtures()
    {
        $user = User::create(['id' => static::ID, 'name' => 'test']);
        $post = Post::create(['id' => static::ID, 'text' => 'test post', 'user_id' => $user->id]);

        $commenter = User::create(['id' => 'test uuid', 'name' => 'test user 1']);

        $commenter->comments()->createMany([
            [
                'id' => 'comment 1',
                'post_id' => $post->id,
                'text' => 'post 1',
            ],
            [
                'id' => 'comment 2',
                'post_id' => $post->id,
                'text' => 'post 2',
            ],
            [
                'id' => 'comment 3',
                'post_id' => $post->id,
                'text' => 'post 3',
            ],
            [
                'id' => 'comment 4',
                'post_id' => $post->id,
                'text' => 'post 4',
            ],
            [
                'id' => 'comment 5',
                'post_id' => $post->id,
                'text' => 'post 5',
            ],
        ]);

        return [$user->fresh(), $post->fresh(), $commenter->fresh()];
    }
}
