<?php

namespace App\Cache\Models;

use App\Cache\Fetchers\ArticleElementFetcher;

class ArticleElementCache extends CacheModel
{
    protected $fetcher = ArticleElementFetcher::class;
    protected $cacheKeyPattern = 'articles:{key}:elements';
}
