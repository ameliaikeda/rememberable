<?php

namespace Amelia\Rememberable;

use Amelia\Rememberable\Query\Builder as QueryBuilder;
use Amelia\Rememberable\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

trait Rememberable
{
    protected static function bootRememberable()
    {
        if (static::rememberable()) {
            static::saving(function (Model $model) {
                $model->flush(get_class($model).':'.$model->getKey());
            });
        }
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        $builder = new QueryBuilder($conn, $grammar, $conn->getPostProcessor(), $this);

        if (isset($this->rememberPrefix)) {
            $builder->cachePrefix($this->rememberPrefix);
        }

        if (static::rememberable()) {
            $builder->remember(-1);
        }

        return $builder;
    }

    /**
     * Override the eloquent query builder.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Amelia\Rememberable\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }

    /**
     * Check if we're "rememberable".
     *
     * @return bool
     */
    public static function rememberable()
    {
        return isset(static::$rememberable) && static::$rememberable === true;
    }
}
