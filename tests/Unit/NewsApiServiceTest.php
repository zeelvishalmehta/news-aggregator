<?php

namespace Tests\Unit;

use App\Services\NewsApiService;
use App\Models\Source;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsApiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Source::factory()->create(['slug' => 'newsapi']);
    }

    /** @test */
    public function it_fetches_and_stores_articles()
    {
        // Mock API response
        Http::fake([
            'newsapi.org/*' => Http::response([
                'articles' => [
                    [
                        'source' => ['name' => 'CNN'],
                        'author' => 'John Doe',
                        'title' => 'Test Article',
                        'description' => 'Test Description',
                        'url' => 'https://example.com/test-article',
                        'urlToImage' => 'https://example.com/image.jpg',
                        'publishedAt' => now()->toIso8601String(),
                        'content' => 'Test content',
                    ]
                ]
            ], 200)
        ]);

        $service = new NewsApiService();
        $service->fetchArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'url' => 'https://example.com/test-article'
        ]);
    }
}
