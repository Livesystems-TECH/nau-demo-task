<?php

namespace App\Cache;

use App\Cache\Contracts\CacheHandlerContract;
use App\Cache\Events\CacheMissing;
use App\Cache\Exceptions\NotFoundException;
use App\Cache\Exceptions\RelationNotFoundException;
use App\Cache\Models\CacheModel;
use Illuminate\Support\Arr;

class CacheQueryBuilder
{
    /**
     * @var CacheModel
     */
    protected $model;

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    protected $cacheKeys = [];

    /**
     * @var CacheHandlerInterface
     */
    protected $cacheHandler;

    /**
     * CacheQueryBuilder constructor.
     *
     * @param CacheHandlerContract $cacheHandler
     */
    public function __construct(CacheHandlerContract $cacheHandler)
    {
        $this->cacheHandler = $cacheHandler;
    }

    /**
     * with('relation')
     * with('relation1', 'relation2')
     * with(['relation1', 'relation2'])
     *
     * @param mixed $relations
     * @return CacheQueryBuilder
     */
    public function with($relations): self
    {
        if (!is_array($relations)) {
            $relations = func_get_args();
        }

        $this->with = $this->parseWithRelations($relations);

        return $this;
    }

    /**
     * find()
     * find(1)
     * find(1,2)
     * find([1, 2])
     *
     * @param $ids
     * @return mixed
     * @throws RelationNotFoundException
     */
    public function find($ids = null)
    {
        // Automagically decide if user wants one or multiple elements
        $single = false;
        if (!is_array($ids)) {
            $single = func_num_args() <= 1;
            $ids = $ids === null ? [null] : func_get_args();
        }

        $items = $this->prepareItems($ids);
        $this->fetchModels($items);
        $this->fetchRelations($items);

        // Merge everything together
        $merged = array_map(function (CacheItem $item) {
            return $item->toArray();
        }, $items);

        return $single ? Arr::first($merged) : $merged;
    }

    /**
     * find()
     * find(1)
     * find(1,2)
     * find([1, 2])
     *
     * @param $ids
     * @return mixed
     * @throws RelationNotFoundException
     * @throws NotFoundException
     */
    public function findOrFail($ids = null)
    {
        // Automagically decide if user wants one or multiple elements
        $single = false;
        if (!is_array($ids)) {
            $single = func_num_args() <= 1;
            $ids = $ids === null ? [null] : func_get_args();
        }

        $items = $this->prepareItems($ids);
        $this->fetchModels($items, true);
        $this->fetchRelations($items);

        // Merge everything together
        $merged = array_map(function (CacheItem $item) {
            return $item->toArray();
        }, $items);

        return $single ? Arr::first($merged) : $merged;
    }

    /**
     * @param CacheModel $model
     */
    public function setModel(CacheModel $model): void
    {
        $this->model = $model;
    }

    /**
     * @param $key
     * @param array $data
     */
    public function update($key, array $data): void
    {
        $this->cacheHandler->put([$key => $data]);
    }

    public function delete($ids = null)
    {
        if (!is_array($ids)) {
            $ids = $ids === null ? [null] : func_get_args();
        }

        $items = $this->prepareItems($ids);

        $keys = array_map(function ($item) {
            return $item->key();
        }, $items);

        return $this->cacheHandler->delete($keys);
    }

    /**
     * @param array $relations
     * @return array
     */
    protected function parseWithRelations(array $relations): array
    {
        $results = [];

        foreach ($relations as $name) {
            Arr::set($results, $name, []);
        }

        return $results;
    }

    /**
     * @param CacheItem[] $items
     * @param bool $withExceptions
     * @return mixed
     * @throws NotFoundException
     */
    protected function fetchModels(array $items, bool $withExceptions = false)
    {
        $cacheKeys = new CacheKeyCollection();

        $this->beforeFetch($items, $cacheKeys);

        $cacheResults = array_map(function ($result) {
            return new CacheResult($result);
        }, $this->cacheHandler->fetch($cacheKeys->get()));

        $this->afterFetch($items, $cacheResults, $withExceptions);

        return $items;
    }

    /**
     * @param CacheItem[] $models
     * @throws RelationNotFoundException
     */
    protected function fetchRelations(array $models)
    {
        foreach ($this->with as $name => $nested) {
            $instances = [];
            $cacheKeys = new CacheKeyCollection();

            if (!method_exists($this->model, $name)) {
                throw new RelationNotFoundException('Relation [' . $name . '] does not exist on model [' . get_class($this->model) . ']');
            }

            foreach ($models as $key => $model) {
                $instance = $this->model->{$name}();
                $instances[$key] = $instance;

                $instance->beforeFetch($model, $cacheKeys);
            }

            $results = $this->model->{$name}()->newQuery()->with(array_keys(Arr::dot($nested)))->find($cacheKeys->get());

            $results = array_map(function ($result) {
                return new CacheResult($result);
            }, $results);

            foreach ($models as $key => $model) {
                $instance = $instances[$key];
                $instance->afterFetch($model, $results);
            }
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function prepareItems(array $ids): array
    {
        return array_map(function ($id) {
            $item = new CacheItem($this->model, $id);

            foreach ($this->with as $name => $nested) {
                $item->addRelation($name, $nested);
            }

            return $item;
        }, $ids);
    }

    /**
     * @param CacheItem[] $items
     * @param CacheResult[] $cacheResults
     * @param bool $withExceptions
     * @throws NotFoundException
     */
    protected function fetchEmptyResults($items, $cacheResults, bool $withExceptions = false)
    {
        foreach ($items as $item) {
            $cacheResult = $cacheResults[$item->resultIndex()];

            if ($cacheResult->result === null) {
                $data = Arr::first($item->model()->newFetcher()->fetch([$item->id()]));
                $cacheResults[$item->resultIndex()]->result = $data;

                if ($data === null) {
                    if ($withExceptions) {
                        throw new NotFoundException($item->model(), $item->id());
                    }
                } else {
                    event(new CacheMissing($item->model(), $item->id(), $data));
                }
            }
        }
    }

    /**
     * @param CacheItem[] $items
     * @param CacheKeyCollection $cacheKeys
     */
    protected function beforeFetch(array $items, CacheKeyCollection $cacheKeys)
    {
        foreach ($items as $item) {
            $item->beforeFetch($cacheKeys);
        }
    }

    /**
     * @param CacheItem[] $items
     * @param CacheResult[] $cacheResults
     * @param bool $withExceptions
     * @throws NotFoundException
     */
    protected function afterFetch(array $items, array $cacheResults, bool $withExceptions = false)
    {
        $this->fetchEmptyResults($items, $cacheResults, $withExceptions);

        foreach ($items as $item) {
            $item->afterFetch($cacheResults);
        }
    }
}
