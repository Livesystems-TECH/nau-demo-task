<?php

namespace App\Cache\Contracts;

interface CacheHandlerContract
{
    public function fetch(array $keys);

    public function put(array $data);

    public function delete(array $keys);
}
