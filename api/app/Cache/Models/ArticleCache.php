<?php

namespace App\Cache\Models;

use App\Cache\Fetchers\ArticleFetcher;
use App\Cache\Models\Location\LocationCache;

class ArticleCache extends CacheModel
{
    protected $fetcher = ArticleFetcher::class;
    protected $cacheKeyPattern = 'articles:{key}';

    public function elements()
    {
        return $this->hasOne(ArticleElementCache::class);
    }
}
