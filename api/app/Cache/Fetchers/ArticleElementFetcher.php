<?php

namespace App\Cache\Fetchers;

use App\Article\Models\Article;
use App\Cache\Contracts\FetchesCachableData;

class ArticleElementFetcher implements FetchesCachableData
{
    public function fetch(array $identifiers): array
    {
        $articles = Article::findMany($identifiers);

        return $articles->mapWithKeys(function (Article $article) {
            return [
                // @todo
            ];
        })->toArray();
    }
}
