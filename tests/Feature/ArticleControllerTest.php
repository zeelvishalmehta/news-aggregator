<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $source;
    protected $category;
    protected $author;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authenticated tests
        $this->user = User::factory()->create();

        // models
        $this->source   = Source::factory()->create(['slug' => 'newsapi', 'name' => 'NewsAPI']);
        $this->category = Category::factory()->create(['slug' => 'sports', 'name' => 'Sports']);
        $this->author   = Author::factory()->create(['name' => 'John Doe']);

        // Create 20 older articles
        Article::factory()
            ->withRelations([
                'source_id'   => $this->source->id,
                'category_id' => $this->category->id,
                'author_id'   => $this->author->id,
            ])
            ->count(20)
            ->create(['published_at' => now()->subDays(2)]);
    }

    /** @test */

    //If token is not generated
    public function it_requires_authentication_for_api_routes()
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Unauthenticated.'
                 ]);
    }

    /** @test */
    public function it_returns_paginated_articles_for_authenticated_user()
    {
        Sanctum::actingAs($this->user, ['*']);

        $perPage = 5;
        $response = $this->getJson("/api/articles?per_page={$perPage}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'current_page',
                         'data' => [
                             '*' => [
                                 'id',
                                 'title',
                                 'slug',
                                 'description',
                                 'content',
                                 'url',
                                 'image_url',
                                 'language',
                                 'published_at',
                                 'category' => ['id', 'name', 'slug'],
                                 'author'   => ['id', 'name'],
                                 'source'   => ['id', 'name', 'slug'],
                             ]
                         ],
                         'first_page_url',
                         'last_page_url',
                         'per_page',
                         'total',
                     ],
                 ]);

        $this->assertCount($perPage, $response->json('data.data'));
    }

    /** @test */
    public function it_can_filter_articles_by_source_category_author_and_date()
    {
        Sanctum::actingAs($this->user, ['*']);
        $perPage = 20;

        // Add today's article
        Article::factory()
            ->withRelations([
                'source_id'   => $this->source->id,
                'category_id' => $this->category->id,
                'author_id'   => $this->author->id,
            ])
            ->create([
                'published_at' => now(),
                'title' => 'Today Sports News',
            ]);

        // Source filter
        $response = $this->getJson("/api/articles?source=newsapi&per_page={$perPage}");
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.data'));

        // Category filter
        $response = $this->getJson("/api/articles?category=sports&per_page={$perPage}");
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.data'));

        // Author filter
        $response = $this->getJson("/api/articles?author=John Doe&per_page={$perPage}");
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.data'));

        // Date range filter
        $today = now()->toDateString();
        $response = $this->getJson("/api/articles?date_from={$today}&date_to={$today}");
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.data'));
    }

    /** @test */
    public function it_can_search_articles_by_keyword()
    {
        Sanctum::actingAs($this->user, ['*']);

        Article::factory()
            ->withRelations([
                'source_id'   => $this->source->id,
                'category_id' => $this->category->id,
                'author_id'   => $this->author->id,
            ])
            ->create(['title' => 'Best UK universities for education – league table']);

        $response = $this->getJson("/api/articles?q=Best UK universities for education – league table");
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_article()
    {
        Sanctum::actingAs($this->user, ['*']);

        $article = Article::factory()
            ->withRelations([
                'source_id'   => $this->source->id,
                'category_id' => $this->category->id,
                'author_id'   => $this->author->id,
            ])
            ->create(['title' => 'We ask the experts']);

        $response = $this->getJson("/api/articles/{$article->id}");
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'id',
                         'title',
                         'slug',
                         'description',
                         'content',
                         'url',
                         'image_url',
                         'language',
                         'published_at',
                         'category' => ['id', 'name', 'slug'],
                         'author'   => ['id', 'name'],
                         'source'   => ['id', 'name', 'slug'],
                     ],
                 ]);
    }
}
