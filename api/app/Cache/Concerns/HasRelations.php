<?php

namespace App\Cache\Concerns;

use App\Cache\Relations\BelongsTo;
use App\Cache\Relations\BelongsToMany;
use App\Cache\Relations\HasOne;

trait HasRelations
{
    protected function hasOne(string $related)
    {
        return new HasOne($related);
    }

    protected function belongsTo(string $related, $localKey = null)
    {
        return new BelongsTo($related, $localKey);
    }

    protected function belongsToMany($related, $arrayKey = '*')
    {
        return new BelongsToMany($related, $arrayKey);
    }
}
