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
            static::saved(function (Model $model) {
                $model->flush(get_class($model).':'.$model->getKey());
            });

            static::deleted(function (Model $model) {
                $model->flush(get_class($model).':'.$model->getKey());
            });
        }
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::rememberable() && static::interceptable() && in_array($method, ['increment', 'decrement'])) {
            $result = call_user_func_array([$this, $method], $parameters);

            $this->fireModelEvent('saved');

            return $result;
        }

        return parent::__call($method, $parameters);
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
        if (! isset(static::$rememberable)) {
            return false;
        }

        return (bool) static::$rememberable;
    }

    /**
     * Check if we're allowed to intercept __call.
     *
     * @return bool
     */
    public static function interceptable()
    {
        if (! isset(static::$interceptable)) {
            return true;
        }

        return (bool) static::$interceptable;
    }

    /**
     * Determine if the model touches a given relation.
     *
     * @param  string  $relation
     * @return bool
     */
    public function touches($relation)
    {
        if (static::rememberable()) {
            return true;
        }

        return parent::touches($relation);
    }
}
