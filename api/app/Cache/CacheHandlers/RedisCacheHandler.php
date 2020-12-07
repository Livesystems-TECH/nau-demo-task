<?php

namespace App\Cache\CacheHandlers;

use App\Cache\Contracts\CacheHandlerContract;
use App\Cache\Contracts\Compressor;
use Exception;

class RedisCacheHandler implements CacheHandlerContract
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * RedisCacheHandler constructor.
     *
     * @throws Exception
     */
    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function fetch(array $keys)
    {
        if (empty($keys)) {
            return [];
        }

        return array_map(function ($value) {
            if ($value === null) {
                return null;
            }

            $value = $this->decode($value);

            return $value;
        }, $this->redis->mGet($keys));
    }

    public function put(array $data)
    {
        $this->redis->mSet(array_map(function ($value) {
            return $this->encode($value);
        }, $data));
    }

    public function delete(array $keys)
    {
        if (empty($keys)) {
            return [];
        }

        $this->redis->del($keys);
    }

    protected function decode(string $value)
    {
        return json_decode($value, true);
    }

    protected function encode(array $value)
    {
        return json_encode($value);
    }
}
