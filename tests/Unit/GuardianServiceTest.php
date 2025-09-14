<?php

namespace Tests\Unit;

use App\Services\GuardianService;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GuardianServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Source::factory()->create(['slug' => 'guardian']);
    }

    /** @test */
    public function it_fetches_and_stores_guardian_articles()
    {
        Http::fake([
            'content.guardianapis.com/*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'id' => 'test-guardian-1',
                            'webTitle' => 'Guardian Test Article',
                            'webUrl' => 'https://guardian.com/test-article',
                            'webPublicationDate' => now()->toIso8601String(),
                            'fields' => [
                                'byline' => 'Jane Smith',
                                'trailText' => 'Guardian Description',
                                'body' => 'Guardian Content',
                                'thumbnail' => 'https://guardian.com/image.jpg'
                            ],
                            'sectionName' => 'Technology',
                            'tags' => []
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = new GuardianService();
        $service->fetchArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Guardian Test Article',
            'url' => 'https://guardian.com/test-article'
        ]);
    }
}
