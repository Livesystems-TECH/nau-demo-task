<?php

namespace App\Cache;

class CacheResult
{
    /**
     * @var mixed
     */
    public $result;

    /**
     * CacheResult constructor.
     *
     * @param mixed $result
     */
    public function __construct($result)
    {
        $this->result = $result;
    }
}
