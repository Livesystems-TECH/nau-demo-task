<?php

namespace Tests\Feature;

use App\Article;
use App\Cache\Models\ArticleCache;
use App\Http\Resources\ArticleResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function allArticlesCanBeListed()
    {
        // Preparation
        $articles = factory(Article::class, 2)->create();

        // Perform action
        $this->get('/api/articles')->assertSuccessful()->assertJson([
            'data' => ArticleResource::collection($articles)->resolve(),
        ]);
    }

    /** @test */
    public function singleArticlesCanBeShown()
    {
        // Preparation
        $article = factory(Article::class)->create();

        // Perform action
        $this->get("/api/articles/{$article->id}")->assertSuccessful()->assertJson([
            'data' => ArticleResource::make($article)->resolve(),
        ]);
    }

    /** @test */
    public function articleCanBeCreated()
    {
        // Preparation
        $data = ['title' => 'Hello World'];

        // Perform action
        $this->postJson('/api/articles', $data)->assertStatus(201)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'created_at',
                'updated_at',
            ],
        ]);

        // Assertions
        $this->assertDatabaseHas('articles', $data);
    }

    /** @test */
    public function articleCanBeUpdated()
    {
        // Preparation
        $article = factory(Article::class)->create([
            'title' => 'Hello World',
        ]);
        $data = ['title' => 'Hello Toast'];
        ArticleCache::renew($article->id);

        // Perform action
        $this->putJson("/api/articles/{$article->id}", $data)->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'created_at',
                'updated_at',
            ],
        ]);

        // Database is updated
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Hello Toast',
        ]);

        // Check if cache is updated
        $this->assertEquals('Hello Toast', ArticleCache::find($article->id)['title']);
    }

    /** @test */
    public function articleCanBeDeleted()
    {
        // Preparation
        $article = factory(Article::class)->create();

        // Perform action
        $this->delete("/api/articles/{$article->id}")->assertStatus(204);

        // Assertions
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }
}