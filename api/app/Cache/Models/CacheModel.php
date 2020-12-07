<?php

namespace App\Cache\Models;

use Illuminate\Support\Arr;
use App\Cache\Builder;
use App\Cache\CacheQueryBuilder;
use App\Cache\Concerns\HasRelations;
use App\Cache\Contracts\FetchesCachableData;
use InvalidArgumentException;

abstract class CacheModel
{
    use HasRelations;

    /**
     * @var string
     */
    protected $cacheKeyPattern;

    /**
     * @var FetchesCachableData
     */
    protected $fetcher;

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    public static function query()
    {
        return (new static())->newQuery();
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }

    /**
     * @return string
     */
    public function getCacheKeyPattern(): string
    {
        return $this->cacheKeyPattern;
    }

    /**
     * @return CacheQueryBuilder
     */
    public function newQuery(): CacheQueryBuilder
    {
        return tap(app(CacheQueryBuilder::class))->setModel(new static());
    }

    /**
     * @return FetchesCachableData
     */
    public function newFetcher(): FetchesCachableData
    {
        if (!$this->fetcher) {
            throw new InvalidArgumentException('Fetcher missing for model ' . class_basename($this));
        }

        return app($this->fetcher);
    }

    /**
     * Fetch data from fetcher
     *
     * @param mixed $ids
     * @return mixed
     */
    protected function fetch($ids = null)
    {
        // Automagically decide if user wants one or multiple elements
        $single = false;
        if (!is_array($ids)) {
            $single = func_num_args() === 1;
            $ids = $ids === null ? [null] : func_get_args();
        }

        $results = $this->newFetcher()->fetch($ids);

        // Sort fetched result
        $results = array_map(function ($id) use ($results) {
            return array_key_exists($id, $results) ? $results[$id] : null;
        }, $ids);

        return $single ? Arr::first($results) : $results;
    }

    /**
     * Renew cached data
     *
     * @param mixed $ids
     */
    protected function renew($ids = null): void
    {
        if (!is_array($ids)) {
            $ids = $ids === null ? [null] : func_get_args();
        }

        foreach ($this->fetch($ids) as $key => $result) {
            if($result !== null) {
                $this->update($ids[$key], $result);
            } else {
                $this->delete($ids[$key]);
            }
        }
    }

    /**
     * Set the cached data to given value
     *
     * @param $id
     * @param array $data
     */
    protected function update($id, array $data)
    {
        $this->newQuery()->update($this->key($id), $data);
    }

    /**
     * Patch a subset of the cached data
     *
     * @param $id
     * @param array $data
     */
    protected function patch($id, array $data): void
    {
        $fetchedData = $this->find($id);

        $newData = array_merge($fetchedData, $data);

        $this->newQuery()->update($this->key($id), $newData);
    }

    /**
     * Generate cache key
     *
     * @param $identifier
     * @return string
     */
    protected function key($identifier): string
    {
        return str_replace('{key}', $identifier, $this->getCacheKeyPattern());
    }
}
