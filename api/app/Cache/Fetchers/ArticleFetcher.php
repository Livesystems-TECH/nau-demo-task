<?php

namespace App\Cache\Fetchers;

use App\Article;
use App\Cache\Contracts\FetchesCachableData;

class ArticleFetcher implements FetchesCachableData
{
    public function fetch(array $identifiers): array
    {
        $articles = Article::findMany($identifiers);

        return $articles->mapWithKeys(function (Article $article) {
            return [
                $article->id => $article->getAttributes(),
            ];
        })->toArray();
    }
}
