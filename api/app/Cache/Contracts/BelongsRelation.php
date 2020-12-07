<?php

namespace App\Cache\Contracts;

interface BelongsRelation
{
    public function arrayKey(): string;

    public function cacheKey(): string;
}
