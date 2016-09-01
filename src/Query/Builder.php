<?php

namespace Amelia\Rememberable\Query;

use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Facades\Cache;
use Predis\ClientInterface as RedisClient;

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
        $key = $key ?: $this->getCacheKey();

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
     * Flush the cache for the current model or a given tag name.
     *
     * Can be called using Model::flush()
     *
     * @param  array|string $tags
     * @return bool
     */
    public function flush($tags = null)
    {
        $tags = $this->getCacheTags($tags);

        $this->flushKeysForTags($tags);
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
        return [$this->getCacheKey(), $this->minutes];
    }

    /**
     * Get a unique cache key for the complete query.
     *
     * @param array|null $columns
     * @return string
     */
    public function getCacheKey($columns = null)
    {
        if ($columns !== null) {
            $this->columns = $columns;
        }

        return $this->prefix.':'.($this->key ?: $this->generateCacheKey());
    }

    /**
     * Get the cache tags to use for this.
     *
     * @param string|array|null $tags
     * @return array
     */
    public function getCacheTags($tags = null)
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
     * @return \Illuminate\Cache\Repository
     */
    public function getCache()
    {
        return Cache::driver($this->driver);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
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
    protected function getCached($columns = ['*'])
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

    public function setTagsForKey(array $tags, $key)
    {
        $cache = $this->getCache();

        $store = $cache->getStore();

        if (! $store instanceof RedisStore) {
            // we can't cache forever in this case. we need sets.
            return;
        }

        $connection = $store->connection();

        foreach ($tags as $tag) {
            $segment = $this->getCacheSegmentKey($tag);

            $connection->sadd($store->getPrefix().$segment, $key);
        }
    }

    /**
     * Flush keys for a set of given tags.
     *
     * @param string|array $tags
     */
    public function flushKeysForTags($tags)
    {
        $tags = is_array($tags) ? $tags : func_get_args();

        $cache = $this->getCache();

        $store = $cache->getStore();

        if (! $store instanceof RedisStore) {
            // we can't cache forever in this case. we need sets.
            return;
        }

        $connection = $store->connection();

        foreach ($tags as $tag) {
            $segment = $this->getCacheSegmentKey($tag);

            $this->deleteCacheValues($store->getPrefix().$segment, $connection, $cache);

            $cache->forget($segment);
        }
    }

    /**
     * Forget cache values that are tagged.
     *
     * @param string $key
     * @param \Predis\ClientInterface $connection
     * @param \Illuminate\Contracts\Cache\Repository $store
     */
    protected function deleteCacheValues($key, RedisClient $connection, Repository $store)
    {
        $members = $connection->smembers($key);

        foreach ($members as $member) {
            $store->forget($member);
        }
    }

    /**
     * Get a segment key for a cache tag.
     *
     * @param string $tag
     * @return string
     */
    protected function getCacheSegmentKey($tag)
    {
        return "rememberable-tag:{$tag}:segment";
    }
}
