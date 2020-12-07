<?php

namespace App\Cache\Relations;

use Illuminate\Support\Arr;
use App\Cache\CacheItem;
use App\Cache\CacheKeyCollection;
use App\Cache\Contracts\RelationContract;

class HasOne extends Relation implements RelationContract
{
    /**
     * @var mixed Index of the result
     */
    protected $index;

    /**
     * @var string Dot notated location of local key
     */
    protected $localKey;

    /**
     * HasOne constructor.
     *
     * @param string $related
     * @param string|null $localKey
     */
    public function __construct(string $related, string $localKey = null)
    {
        $this->related = $related;
        $this->localKey = $localKey ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4)[2]['function'];
    }

    /**
     * @param CacheItem $item
     * @param CacheKeyCollection $fetchKeys
     */
    public function beforeFetch(CacheItem $item, CacheKeyCollection $fetchKeys): void
    {
        $this->index = $fetchKeys->add($item->id());
    }

    /**
     * @param CacheItem $item
     * @param array $results
     */
    public function afterFetch(CacheItem $item, array $results): void
    {
        // Add dummy value to later get reference
        Arr::set($item->main->result, $this->localKey, null);
        $reference = &reference($item->main->result, $this->localKey);

        $result = $results[$this->index]->result;

        $reference = $result;
    }
}
