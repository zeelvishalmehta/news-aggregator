<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Source;
use App\Models\Category;
use App\Models\Author;

class ArticleFactory extends Factory
{
    protected $model = \App\Models\Article::class;

    public function definition()
    {
        return [
            'source_id'    => Source::factory(),
            'category_id'  => Category::factory(),
            'author_id'    => Author::factory(),
            'title'        => $this->faker->sentence(),
            'slug'         => $this->faker->slug(),
            'description'  => $this->faker->paragraph(),
            'content'      => $this->faker->paragraphs(3, true),
            'url'          => $this->faker->unique()->url(),
            'image_url'    => $this->faker->imageUrl(),
            'language'     => 'en',
            'published_at' => now(),
        ];
    }

    /**
     * State: optionally use existing related models
     *
     * @param array $relations ['source_id' => int, 'category_id' => int, 'author_id' => int]
     */
    public function withRelations(array $relations = [])
    {
        return $this->state(function () use ($relations) {
            return [
                'source_id'   => $relations['source_id']   ?? Source::factory(),
                'category_id' => $relations['category_id'] ?? Category::factory(),
                'author_id'   => $relations['author_id']   ?? Author::factory(),
            ];
        });
    }
}
