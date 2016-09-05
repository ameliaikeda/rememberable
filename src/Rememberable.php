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
     * Run the increment or decrement method on the model.
     *
     * @param  string  $column
     * @param  int  $amount
     * @param  array  $extra
     * @param  string  $method
     * @return int
     */
    protected function incrementOrDecrement($column, $amount, $extra, $method)
    {
        $result = parent::incrementOrDecrement($column, $amount, $extra, $method);

        if (static::rememberable() && static::interceptable()) {
            $this->fireModelEvent('saved');
        }

        return $result;
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
        return isset(static::$rememberable) && static::$rememberable === true;
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
}
