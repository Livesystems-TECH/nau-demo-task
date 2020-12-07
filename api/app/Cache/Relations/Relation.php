<?php

namespace App\Cache\Relations;

abstract class Relation
{
    /**
     * @var string Related model name
     */
    protected $related;

    public function newQuery()
    {
        return $this->related::query();
    }
}
