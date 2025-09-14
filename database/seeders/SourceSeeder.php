<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Source;


class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $sources = [
            [
                'name' => 'NewsAPI',
                'slug' => 'newsapi',
                'api_name' => 'newsapi',
                'api_key' => env('NEWSAPI_KEY'),
                'base_url' => 'https://newsapi.org/v2/',
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'guardian',
                'api_name' => 'guardian',
                'api_key' => env('GUARDIAN_KEY'),
                'base_url' => 'https://content.guardianapis.com/',
            ],
            [
                'name' => 'New York Times',
                'slug' => 'nyt',
                'api_name' => 'nyt',
                'api_key' => env('NYT_KEY'),
                'base_url' => 'https://api.nytimes.com/svc/',
            ],
        ];

        foreach ($sources as $source) {
            Source::updateOrCreate(
                ['slug' => $source['slug']], // unique key to check
                $source                       // attributes to insert/update
            );
        }
    
    }
}
