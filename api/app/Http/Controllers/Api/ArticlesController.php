<?php

namespace App\Http\Controllers\Api;

use App\Article;
use App\Cache\Models\ArticleCache;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ArticlesController extends Controller
{
    /**
     * List Articles
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $articleIds = Article::pluck('id')->toArray();
        $cached = ArticleCache::find($articleIds);

        $articles = array_map(function (array $cacheData) {
            return tap(new Article())->setRawAttributes($cacheData);
        }, $cached);

        return ArticleResource::collection(collect($articles));
    }

    /**
     * Show a single Article
     *
     * @param int $article
     * @return ArticleResource
     */
    public function show(int $article)
    {
        $cached = ArticleCache::find($article);
        $article = tap(new Article())->setRawAttributes($cached);

        return new ArticleResource($article);
    }

    /**
     * Create a new article
     *
     * @param ArticleRequest $request
     * @return ArticleResource
     */
    public function store(ArticleRequest $request)
    {
        $article = Article::create($request->validated());

        return new ArticleResource($article);
    }

    /**
     * Update an existing article
     *
     * @param ArticleRequest $request
     * @param Article $article
     * @return ArticleResource
     */
    public function update(ArticleRequest $request, Article $article)
    {
        $article->update($request->validated());

        return new ArticleResource($article);
    }

    /**
     * Delete an existing article
     *
     * @param Article $article
     * @return Response
     * @throws Exception
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return response()->noContent();
    }
}
