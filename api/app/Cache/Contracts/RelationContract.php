<?php

namespace App\Cache\Contracts;

use App\Cache\CacheItem;
use App\Cache\CacheKeyCollection;

interface RelationContract
{
    /**
     * @param CacheItem $item
     * @param CacheKeyCollection $fetchKeys
     */
    public function beforeFetch(CacheItem $item, CacheKeyCollection $fetchKeys): void;

    /**
     * @param CacheItem $item
     * @param CacheResult[] $results
     */
    public function afterFetch(CacheItem $item, array $results): void;
}

