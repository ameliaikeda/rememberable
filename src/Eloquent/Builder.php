<?php

namespace Amelia\Rememberable\Eloquent;

use Illuminate\Database\Eloquent\Collection;

class Builder extends \Illuminate\Database\Eloquent\Builder
{
    /**
     * @var \Amelia\Rememberable\Query\Builder
     */
    protected $query;

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function getModels($columns = ['*'])
    {
        $key = $this->query->getCacheKey($columns);

        $results = $this->query->get($columns)->all();

        $connection = $this->model->getConnectionName();

        $models = $this->model->hydrate($results, $connection);

        $this->tagModels($models, $key);

        return $models->all();
    }

    /**
     * Tag this cache key with the ID of every model in the collection.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[] $models
     * @param string $key
     */
    protected function tagModels(Collection $models, $key)
    {
        $tags = $this->query->getCacheTags();

        $class = get_class($this->model);

        foreach ($models as $model) {
            $tags[] = $class.':'.$model->getKey();
        }

        $this->query->setTagsForKey($tags, $key);
    }
}
