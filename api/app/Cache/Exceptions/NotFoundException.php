<?php

namespace App\Cache\Exceptions;

use App\Cache\Models\CacheModel;
use Exception;

class NotFoundException extends Exception
{
    public $model;
    public $id;

    public function __construct(CacheModel $model, $id)
    {
        $this->model = $model;
        $this->id = $id;
        $modelName = get_class($model);

        parent::__construct("Unable to fetch model [{$modelName}] with id [{$id}]");
    }
}
