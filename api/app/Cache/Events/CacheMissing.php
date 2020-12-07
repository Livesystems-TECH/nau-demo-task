<?php

namespace App\Cache\Events;

use App\Cache\Models\CacheModel;

class CacheMissing
{
    /**
     * @var CacheModel
     */
    public $model;

    /**
     * @var mixed
     */
    public $id;

    /**
     * @var array
     */
    public $data;

    /**
     * CacheMissing constructor.
     *
     * @param CacheModel $model
     * @param $id
     * @param array $data
     */
    public function __construct(CacheModel $model, $id, array $data)
    {
        $this->model = $model;
        $this->id = $id;
        $this->data = $data;
    }
}
