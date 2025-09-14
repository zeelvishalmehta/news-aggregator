<?php

namespace Tests\Unit;

use App\Services\NewYorkTimesService;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewYorkTimesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Source::factory()->create(['slug' => 'nyt']);
    }

    /** @test */
    public function it_fetches_and_stores_nyt_articles()
    {
        Http::fake([
            'api.nytimes.com/*' => Http::response([
                'results' => [
                    [
                        'title' => 'NYT Test Article',
                        'abstract' => 'NYT Description',
                        'url' => 'https://nytimes.com/test-article',
                        'byline' => 'By Alan Doe',
                        'section' => 'World',
                        'multimedia' => [
                            ['url' => 'https://nytimes.com/image.jpg']
                        ],
                        'published_date' => now()->toIso8601String()
                    ]
                ]
            ], 200)
        ]);

        $service = new NewYorkTimesService();
        $service->fetchArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'NYT Test Article',
            'url' => 'https://nytimes.com/test-article'
        ]);
    }
}
