<?php

namespace App\Cache\Contracts;

interface FetchesCachableData
{
    /**
     * Fetch an array of identifiers
     *
     * @param array $identifiers
     * @return array
     */
    public function fetch(array $identifiers): array;
}
