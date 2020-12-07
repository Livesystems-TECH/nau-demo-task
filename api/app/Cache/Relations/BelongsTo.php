<?php

namespace App\Cache\Relations;

use Illuminate\Support\Arr;
use App\Cache\CacheItem;
use App\Cache\CacheKeyCollection;
use App\Cache\Contracts\RelationContract;
use App\Exceptions\ReferenceNotFoundException;

class BelongsTo extends Relation implements RelationContract
{
    /**
     * @var string Dot notated location of local key
     */
    protected $localKey;

    /**
     * @var string Dot notated location of new local key
     */
    protected $newLocalKey;

    /**
     * @var mixed Reference to local key
     */
    protected $localKeyReference;

    /**
     * @var mixed Reference to new local key
     */
    protected $newLocalKeyReference;

    /**
     * @var int Index in the result set
     */
    protected $resultIndex;

    /**
     * BelongsTo constructor.
     *
     * @param string $related
     * @param string|null $localKey
     * @param string|null $newLocalKey
     */
    public function __construct(string $related, string $localKey = null, string $newLocalKey = null)
    {
        $this->related = $related;
        $this->localKey = $localKey ?? $this->guessLocalKey();
        $this->newLocalKey = $newLocalKey;
    }

    /**
     * @return string|null
     */
    public function localKey(): ?string
    {
        return $this->localKey;
    }

    /**
     * @param CacheItem $item
     * @param CacheKeyCollection $fetchKeys
     * @throws ReferenceNotFoundException
     */
    public function beforeFetch(CacheItem $item, CacheKeyCollection $fetchKeys): void
    {
        $this->localKeyReference = &reference($item->main->result, $this->localKey);

        // If the local value is null there is no need to fetch anything
        if($this->localKeyReference === null) {
            return;
        }

        if ($this->newLocalKey !== null) {
            // set the value to create reference
            Arr::set($item->main->result, $this->newLocalKey, null);
            $this->newLocalKeyReference = &reference($item->main->result, $this->newLocalKey);
        }

        $relationKey = $this->localKeyReference;

        $this->resultIndex = $fetchKeys->add($relationKey);
    }

    /**
     * @param CacheItem $item
     * @param array $results
     */
    public function afterFetch(CacheItem $item, array $results): void
    {
        // If the local value is null there is no need to assign anything
        if($this->localKeyReference === null) {
            return;
        }

        $result = $results[$this->resultIndex]->result;

        // Different new local key was set
        if ($this->newLocalKey !== null) {
            $this->newLocalKeyReference = $result;
        } else {
            $this->localKeyReference = $result;
        }
    }

    /**
     * @return string
     */
    private function guessLocalKey(): ?string
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4)[3]['function'];
    }
}
