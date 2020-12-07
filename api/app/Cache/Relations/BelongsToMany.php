<?php

namespace App\Cache\Relations;

use App\Cache\CacheItem;
use App\Cache\CacheKeyCollection;

class BelongsToMany extends Relation
{
    /**
     * @var string Dot notated location of local key
     */
    protected $localKey;

    /**
     * @var int[] Array of result indexes
     */
    protected $resultIndexes = [];

    /**
     * @var mixed[] Array of references to local keys
     */
    protected $localKeyReferences = [];

    /**
     * BelongsToMany constructor.
     *
     * @param string $related
     * @param string $localKey
     */
    public function __construct(string $related, string $localKey)
    {
        $this->related = $related;
        $this->localKey = $localKey;
    }

    /**
     * @param CacheItem $item
     * @param CacheKeyCollection $fetchKeys
     */
    public function beforeFetch(CacheItem $item, CacheKeyCollection $fetchKeys): void
    {
        $references = &reference($item->main->result, $this->localKey);

        foreach ($references as &$reference) {
            $this->localKeyReferences[] = &$reference;
            $this->resultIndexes[] = $fetchKeys->add($reference);
        }
    }

    /**
     * @param CacheItem $item
     * @param array $results
     */
    public function afterFetch(CacheItem $item, array $results): void
    {
        foreach ($this->resultIndexes as $key => $resultIndex) {
            $this->localKeyReferences[$key] = $results[$resultIndex]->result;
        }
    }
}
