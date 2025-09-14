<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class NewYorkTimesService
{
    protected $baseUrl = 'https://api.nytimes.com/svc/';
    protected $apiKey;
    protected $source;

    public function __construct()
    {
        $this->apiKey = env('NYT_KEY');

        $this->source = Source::where('slug', 'nyt')->first();

        if (!$this->source) {
            throw new \Exception("Source 'newyorktimes' not found in DB. Please seed it first.");
        }
    }

    public function fetchArticles()
    {
        $params = [
            'api-key' => $this->apiKey,
        ];

        // Example endpoint: Top Stories API
        $response = Http::get($this->baseUrl . 'topstories/v2/home.json', $params);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch articles: ' . $response->body());
        }

        $articles = $response->json()['results'] ?? [];

        foreach ($articles as $data) {
            $this->storeArticle($data);
        }

        $this->source->update(['last_fetched_at' => now()]);
    }

    protected function storeArticle($data)
    {
        $authorId = null;

        // Handle author
        if (!empty($data['byline'])) {
            $authorName = str_replace('By ', '', $data['byline']); // remove "By "
            $author = Author::firstOrCreate(['name' => $authorName]);
            $authorId = $author->id;
        } else {
            $author = Author::firstOrCreate(['name' => 'Unknown Author']);
            $authorId = $author->id;
        }

        // Category
        $categoryName = $data['section'] ?? 'General';
        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['slug' => Str::slug($categoryName)]
        );

        $slug = !empty($data['title']) ? Str::slug($data['title']) : null;

        // Image URL (take first multimedia if available)
        $imageUrl = null;
        if (!empty($data['multimedia']) && is_array($data['multimedia'])) {
            foreach ($data['multimedia'] as $media) {
                if (!empty($media['url'])) {
                    $imageUrl = $media['url'];
                    break;
                }
            }
        }

        Article::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'external_id' => $data['url'],
            ],
            [
                'title' => $data['title'] ?? 'No Title',
                'slug' => $slug,
                'description' => $data['abstract'] ?? null,
                'content' => $data['abstract'] ?? null,
                'url' => $data['url'] ?? null,
                'image_url' => $imageUrl,
                'language' => 'en',
                'published_at' => !empty($data['published_date']) ? date('Y-m-d H:i:s', strtotime($data['published_date'])) : null,
                'category_id' => $category->id,
                'author_id' => $authorId,
                'raw' => json_encode($data),
            ]
        );
    }
}
