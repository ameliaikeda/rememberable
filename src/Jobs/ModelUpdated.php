<?php

namespace Amelia\Rememberable\Jobs;

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
        $this->model->flush($this->key($this->model));

        foreach ($this->model->getRelations() as $type => $relation) {
            if ($relation === null) {
                continue;
            }

            $this->model->flush($this->key($relation));
        }
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
