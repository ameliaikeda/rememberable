<?php

namespace Amelia\Rememberable\Jobs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ModelUpdated
{
    /**
     * A given model.
     *
     * @var \Illuminate\Database\Eloquent\Model|\Amelia\Rememberable\Rememberable
     */
    protected $model;

    /**
     * ModelUpdated constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Update a model.
     *
     * @return void
     */
    public function handle()
    {
        $this->flush($this->model);

        foreach ($this->model->getRelations() as $type => $relation) {
            if ($relation === null) {
                continue;
            }

            if ($relation instanceof Collection) {
                $relation->each(function (Model $model) {
                    $this->flush($model);
                });
            } else {
                $this->flush($relation);
            }
        }
    }

    /**
     * Flush the cache for a given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function flush(Model $model)
    {
        $this->model->flush($this->key($model));
    }

    /**
     * Get a cache key for this model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    protected function key(Model $model)
    {
        return get_class($model).':'.$model->getKey();
    }
}
