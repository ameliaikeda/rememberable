<?php

namespace Amelia\Rememberable\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

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

        $models = parent::getModels($columns);

        $this->tagModels($models, $key);

        return $models;
    }

    /**
     * Tag this cache key with the ID of every model in the collection.
     *
     * @param array|\Illuminate\Database\Eloquent\Model[] $models
     * @param string $key
     */
    protected function tagModels($models, $key)
    {
        $tags = $this->query->getCacheTags();

        $class = get_class($this->model);

        foreach ($models as $model) {
            $tags[] = $class.':'.$model->getKey();
        }

        $this->query->setTagsForKey($tags, $key);
    }
}
