<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    protected $model = \App\Models\Author::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
