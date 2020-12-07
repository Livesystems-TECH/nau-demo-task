<?php

namespace App\Cache;

use App\Cache\Models\CacheModel;

class CacheItem
{
    /**
     * @var CacheResult
     */
    public $main;

    /**
     * @var mixed Identifier
     */
    protected $id;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var CacheModel
     */
    protected $model;

    /**
     * @var int Index in the result set
     */
    protected $resultIndex;

    /**
     * CacheItem constructor.
     *
     * @param CacheModel $model
     * @param mixed $id
     */
    public function __construct(CacheModel $model, $id)
    {
        $this->model = $model;
        $this->id = $id;
    }

    /**
     * @param CacheKeyCollection $fetchKeys
     */
    public function beforeFetch(CacheKeyCollection $fetchKeys)
    {
        $this->resultIndex = $fetchKeys->add($this->key());
    }

    /**
     * @param CacheResult[] $results
     */
    public function afterFetch(array $results)
    {
        $this->main = $results[$this->resultIndex];
    }

    public function key()
    {
        return $this->replaceKey($this->id());
    }

    /**
     * @param string $name
     * @param array $nested
     */
    public function addRelation(string $name, array $nested): void
    {
        $this->relations[$name] = $nested;
    }

    /**
     * @return array
     */
    public function toArray(): ?array
    {
        return $this->main->result;
    }

    /**
     * Getter for identifier
     *
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    public function model(): CacheModel
    {
        return $this->model;
    }

    public function resultIndex(): int
    {
        return $this->resultIndex;
    }

    /**
     * @param $key
     * @return string
     */
    protected function replaceKey($key): string
    {
        return str_replace('{key}', $key, $this->model->getCacheKeyPattern());
    }
}
