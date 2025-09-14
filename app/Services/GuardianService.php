<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class GuardianService
{
    protected $baseUrl = 'https://content.guardianapis.com/';
    protected $apiKey;
    protected $source;

    public function __construct()
    {
        $this->apiKey = env('GUARDIAN_KEY');

        $this->source = Source::where('slug', 'guardian')->first();

        if (!$this->source) {
            throw new \Exception("Source 'guardian' not found in DB. Please seed it first.");
        }
    }

    public function fetchArticles()
    {
        $params = [
            'api-key' => $this->apiKey,
            'show-fields' => 'headline,trailText,body,thumbnail,byline',
            'show-tags' => 'contributor',
            'page-size' => 50,
        ];

        $response = Http::get($this->baseUrl . 'search', $params);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch articles: ' . $response->body());
        }

        $articles = $response->json()['response']['results'] ?? [];

        foreach ($articles as $data) {
            $this->storeArticle($data);
        }

        // Update last fetched timestamp
        $this->source->update(['last_fetched_at' => now()]);
    }

    protected function storeArticle($data)
    {
        $authorId = null;

        // 1️⃣ Check fields.byline first
        if (!empty($data['fields']['byline'])) {
            $author = Author::firstOrCreate(['name' => $data['fields']['byline']]);
            $authorId = $author->id;
        }
        // 2️⃣ Check tags for contributor
        elseif (!empty($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                if (isset($tag['type']) && $tag['type'] === 'contributor') {
                    $author = Author::firstOrCreate(['name' => $tag['webTitle']]);
                    $authorId = $author->id;
                    break;
                }
            }
        }
        // 3️⃣ Fallback
        if (!$authorId) {
            $author = Author::firstOrCreate(['name' => 'Unknown Author']);
            $authorId = $author->id;
        }

        // Category
        $category = Category::firstOrCreate(
            ['name' => $data['sectionName'] ?? 'General'],
            ['slug' => Str::slug($data['sectionName'] ?? 'general')]
        );

        $slug = !empty($data['webTitle']) ? Str::slug($data['webTitle']) : null;

        // Save Article
        Article::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'external_id' => $data['id'],
            ],
            [
                'title' => $data['webTitle'] ?? 'No Title',
                'slug' => $slug,
                'description' => $data['fields']['trailText'] ?? null,
                'content' => $data['fields']['body'] ?? null,
                'url' => $data['webUrl'] ?? null,
                'image_url' => $data['fields']['thumbnail'] ?? null,
                'language' => 'en',
                'published_at' => !empty($data['webPublicationDate']) ? date('Y-m-d H:i:s', strtotime($data['webPublicationDate'])) : null,
                'category_id' => $category->id,
                'author_id' => $authorId,
                'raw' => json_encode($data),
            ]
        );
    }
}
