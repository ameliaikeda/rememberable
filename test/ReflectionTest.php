<?php

use Amelia\Rememberable\Jobs\CheckForRelations;

class ReflectionTest extends RememberTestCase
{
    public function testBasicReflection()
    {
        $model = new ReflectionUser;

        $job = new CheckForRelations($model);
        $job->handle();

        $this->assertCount(3, $relations = $model->getRememberableRelations());
        $this->assertArrayHasKey('groups', $relations);
        $this->assertArrayHasKey('comments', $relations);
        $this->assertArrayHasKey('posts', $relations);
    }
}
