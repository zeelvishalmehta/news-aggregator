<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class NewsApiService
{
    protected $apiKey;
    protected $baseUrl;
    protected $source;

    public function __construct()
    {
        $this->apiKey = env('NEWSAPI_KEY');
        $this->baseUrl = 'https://newsapi.org/v2/';
        $this->source = Source::where('slug', 'newsapi')->first();

        if (!$this->source) {
            throw new \Exception("Source 'newsapi' not found in DB. Please seed it first.");
        }
    }

    public function fetchArticles()
    {
        $params = [
            'apiKey' => $this->apiKey,
            'language' => 'en',
            'pageSize' => 20,
        ];

        $response = Http::get($this->baseUrl . 'top-headlines', $params);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch articles: ' . $response->body());
        }

        $articles = $response->json()['articles'] ?? [];

        foreach ($articles as $data) {
            $this->storeArticle($data);
        }

        $this->source->update(['last_fetched_at' => now()]);
    }

    protected function storeArticle($data)
    {
        // --- Author ---
        $author = null;
        if (!empty($data['author'])) {
            $author = Author::firstOrCreate(['name' => $data['author']]);
        } else {
            $author = Author::firstOrCreate(['name' => 'Unknown Author']);
        }

        // --- Category ---
        $category = null;
        if (!empty($data['category'])) {
            $category = Category::firstOrCreate(
                ['name' => $data['category']],
                ['slug' => Str::slug($data['category'])]
            );
        } else {
            $category = Category::firstOrCreate(
                ['name' => 'General'],
                ['slug' => 'general']
            );
        }

        $slug = !empty($data['title']) ? Str::slug($data['title']) : null;

        Article::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'external_id' => $data['url']
            ],
            [
                'title' => $data['title'] ?? 'No Title',
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'content' => $data['content'] ?? null,
                'url' => $data['url'],
                'image_url' => $data['urlToImage'] ?? null,
                'language' => $data['language'] ?? 'en',
                'published_at' => !empty($data['publishedAt']) ? date('Y-m-d H:i:s', strtotime($data['publishedAt'])) : null,
                'category_id' => $category->id,
                'author_id' => $author->id,
                'raw' => json_encode($data),
            ]
        );
    }
}
