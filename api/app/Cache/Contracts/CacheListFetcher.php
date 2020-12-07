<?php

namespace App\Cache\Contracts;

interface CacheListFetcher
{
    /**
     * Fetch an array of identifiers
     *
     * @param mixed|null $identifier
     * @return array
     */
    public function fetch($identifier = null): array;
}
