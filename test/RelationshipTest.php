<?php

use Illuminate\Support\Facades\Redis;

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

    public function testHasOneOrMany()
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

    public function testBelongsToMany_Attach()
    {
        $user = User::create(['id' => 'user 1', 'name' => 'test user']);

        Group::create(['id' => 'group 1', 'name' => 'group 1']);
        Group::create(['id' => 'group 2', 'name' => 'group 2']);
        Group::create(['id' => 'group 3', 'name' => 'group 3']);
        Group::create(['id' => 'group 4', 'name' => 'group 4']);
        Group::create(['id' => 'group 5', 'name' => 'group 5']);

        $user->groups()->sync(['group 1', 'group 2', 'group 3', 'group 4']);

        $user = $user->fresh();

        $groups = $user->groups;

        $user = $user->fresh();

        static::$sql = false;

        $this->assertEquals($groups, $user->groups);

        static::$sql = true;

        $user = $user->fresh();

        $groups = $user->groups;

        $user->groups()->attach('group 5');

        $user = $user->fresh();

        $this->assertCount(4, $groups);
        $this->assertCount(5, $user->groups);
    }

    public function testBelongsToMany_Detach()
    {
        $user = User::create(['id' => 'user 1', 'name' => 'test user']);

        Group::create(['id' => 'group 1', 'name' => 'group 1']);
        Group::create(['id' => 'group 2', 'name' => 'group 2']);
        Group::create(['id' => 'group 3', 'name' => 'group 3']);
        Group::create(['id' => 'group 4', 'name' => 'group 4']);
        Group::create(['id' => 'group 5', 'name' => 'group 5']);

        $user->groups()->sync(['group 1', 'group 2', 'group 3', 'group 4']);

        $user = $user->fresh();

        $groups = $user->groups;

        $user = $user->fresh();

        static::$sql = false;

        $this->assertEquals($groups, $user->groups);

        static::$sql = true;

        $user = $user->fresh();

        $groups = $user->groups;

        $user->groups()->detach('group 3');

        $user = $user->fresh();

        $this->assertCount(4, $groups);
        $this->assertCount(3, $user->groups);
    }

    public function testHasMany_Addition()
    {
        list($user, $post, $commenter) = $this->fixtures();

        $comments = $commenter->comments;

        $commenter->comments()->create([
            'id' => 'new comment',
            'post_id' => $post->id,
            'text' => 'This should be present!',
        ]);

        $commenter = $commenter->fresh();

        $this->assertCount(5, $comments);
        $this->assertCount(6, $commenter->comments);
    }

    public function testHasMany_Deletion()
    {
        list($user, $post, $commenter) = $this->fixtures();

        $comments = $commenter->comments;

        $commenter->comments()->where('id', 'comment 4')->delete();

        $commenter = $commenter->fresh();

        $this->assertCount(5, $comments);
        $this->assertCount(4, $commenter->comments);
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
