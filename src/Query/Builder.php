<?php

namespace Amelia\Rememberable\Query;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Facades\Cache;

class Builder extends \Illuminate\Database\Query\Builder
{
    const HASH_ALGO = 'sha512';
    const BASE_TAG = 'rememberable';
    const VERSION_TAG = 'v3.0.0';

    /**
     * The key that should be used when caching the query.
     *
     * @var string
     */
    protected $key;

    /**
     * The number of minutes to cache the query.
     *
     * @var int
     */
    protected $minutes;

    /**
     * The tags for the query cache.
     *
     * @var array
     */
    protected $tags;

    /**
     * The cache driver to be used.
     *
     * @var string
     */
    protected $driver;

    /**
     * A cache prefix.
     *
     * @var string
     */
    protected $prefix = 'rememberable';

    /**
     * The eloquent model we're caching.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Builder constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param \Illuminate\Database\Query\Grammars\Grammar $grammar
     * @param \Illuminate\Database\Query\Processors\Processor $processor
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(
        ConnectionInterface $connection,
        Grammar $grammar,
        Processor $processor,
        Model $model
    ) {
        $this->model = $model;

        parent::__construct($connection, $grammar, $processor);
    }

    /**
     * Indicate that the query results should be cached.
     *
     * @param  \DateTime|int $minutes
     * @param  string $key
     * @return $this
     */
    public function remember($minutes, $key = null)
    {
        list($this->minutes, $this->key) = [$minutes, $key];

        return $this;
    }

    /**
     * Forget the executed query.
     *
     * Allows a key to be passed in, for `User::forget('my-key')`.
     *
     * @param string $key
     * @return bool
     */
    public function forget($key = null)
    {
        $key = $key ?: $this->getKey();

        $cache = $this->getCache();

        return $cache->forget($key);
    }

    /**
     * Indicate that the query results should be cached forever.
     *
     * @param  string $key
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function rememberForever($key = null)
    {
        return $this->remember(-1, $key);
    }

    /**
     * Indicate that the results, if cached, should use the given cache tags.
     *
     * @param  array|mixed $tags
     * @return $this
     */
    public function tags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Indicate that the results, if cached, should use the given cache driver.
     *
     * @param  string $driver
     * @return $this
     */
    public function driver($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Flush the cache for the current model or a given tag name.
     *
     * Can be called using Model::flush()
     *
     * @param  array|string $tags
     * @return bool
     */
    public function flush($tags = null)
    {
        $cache = Cache::driver($this->driver);

        if (! method_exists($cache, 'tags')) {
            return false;
        }

        $tags = $this->getCacheTags($tags);

        $cache->tags($tags)->flush();

        return true;
    }

    /**
     * Set the cache prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the Closure callback used when caching queries.
     *
     * @param  array $columns
     * @return \Closure
     */
    protected function getCacheCallback($columns)
    {
        return function () use ($columns) {
            $this->minutes = null;

            return $this->get($columns);
        };
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    protected function generateCacheKey()
    {
        $name = $this->connection->getName();

        $data = $name.$this->toSql().serialize($this->getBindings());

        return hash(static::HASH_ALGO, $data);
    }

    /**
     * Get the cache key and cache minutes as an array.
     *
     * @return array
     */
    protected function getCacheInfo()
    {
        return [$this->getKey(), $this->minutes];
    }

    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    protected function getKey()
    {
        return $this->prefix.':'.($this->key ?: $this->generateCacheKey());
    }

    /**
     * Get the cache tags to use for this.
     *
     * @param string|array|null $tags
     * @return array
     */
    protected function getCacheTags($tags = null)
    {
        if ($tags !== null) {
            return (array) $tags;
        }

        $tags = (array) $this->tags;
        $tags[] = static::VERSION_TAG;
        $tags[] = static::BASE_TAG;
        $tags[] = get_class($this->model);
        $tags[] = $this->from;

        return $tags;
    }

    /**
     * Get the cache object with tags assigned, if applicable.
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function getCache()
    {
        $cache = Cache::driver($this->driver);

        if (! method_exists($cache, 'tags')) {
            throw new \RuntimeException('Rememberable requires a tagged store.');
        }

        return $cache->tags($this->getCacheTags());
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     * @return array|static[]
     */
    public function get($columns = ['*'])
    {
        if (! is_null($this->minutes)) {
            return $this->getCached($columns);
        }

        return parent::get($columns);
    }

    /**
     * Execute the query as a cached "select" statement.
     *
     * @param  array $columns
     * @return array
     */
    public function getCached($columns = ['*'])
    {
        if (is_null($this->columns)) {
            $this->columns = $columns;
        }

        // If the query is requested to be cached, we will cache it using a unique key
        // for this database connection and query statement, including the bindings
        // that are used on this query, providing great convenience when caching.
        list($key, $minutes) = $this->getCacheInfo();

        $cache = $this->getCache();

        $callback = $this->getCacheCallback($columns);

        // If the "minutes" value is less than zero, we will use that as the indicator
        // that the value should be remembered values should be stored indefinitely
        // and if we have minutes we will use the typical remember function here.
        if ($minutes < 0) {
            return $cache->rememberForever($key, $callback);
        }

        return $cache->remember($key, $minutes, $callback);
    }
}
