<?php

namespace Amelia\Rememberable;

use Amelia\Rememberable\Query\Builder;

trait Rememberable
{
    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        $builder = new Builder($conn, $grammar, $conn->getPostProcessor(), $this);

        if (isset($this->tags)) {
            $builder->tags($this->tags);
        }

        if (isset($this->prefix)) {
            $builder->prefix($this->prefix);
        }

        return $builder;
    }
}
