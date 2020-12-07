<?php

namespace App\Observers;

use App\Article;
use App\Cache\Models\ArticleCache;

class ArticleObserver
{
    public function saved(Article $article)
    {
        // Renew cache
        ArticleCache::renew($article->id);
    }
}
