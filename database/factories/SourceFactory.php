<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SourceFactory extends Factory
{
    protected $model = \App\Models\Source::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->slug(),
            'last_fetched_at' => now(),
        ];
    }
}
