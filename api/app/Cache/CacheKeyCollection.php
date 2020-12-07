<?php

namespace App\Cache;

class CacheKeyCollection
{
    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @param mixed $key
     * @return int
     */
    public function add($key): int
    {
        return array_push($this->keys, $key) - 1;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->keys;
    }
}
