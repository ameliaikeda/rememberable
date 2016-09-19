<?php

namespace Amelia\Rememberable\Jobs;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionMethod;

class CheckForRelations
{
    /**
     * A list of classes to exclude when checking methods.
     *
     * @var string[]
     */
    const EXCLUDES = [
        \Amelia\Rememberable\Rememberable::class,
        \Illuminate\Database\Eloquent\Model::class,
    ];

    /**
     * The model we're reflecting.
     *
     * @var \Amelia\Rememberable\Rememberable|\Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * CheckForRelations constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model|\Amelia\Rememberable\Rememberable $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        $class = new ReflectionClass($this->model);

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (static::includes($method) && static::applies($method)) {
                $this->model->addRememberableRelation($method->name, $this->model->{$method->name}());
            }
        }
    }

    /**
     * Check if this method is applicable, to speed things up.
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected static function includes(ReflectionMethod $method)
    {
        return ! in_array($method->class, static::EXCLUDES, true) && ! $method->isStatic();
    }

    /**
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected static function applies(ReflectionMethod $method)
    {
        return str_contains($method->getDocComment(), "@relation");
    }
}
