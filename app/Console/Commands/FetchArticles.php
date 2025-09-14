<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsApiService;
use App\Services\GuardianService;
use App\Services\NewYorkTimesService;
use Illuminate\Support\Facades\Log;

class FetchArticles extends Command
{
    protected $signature = 'app:fetch-articles';
    protected $description = 'Fetch articles from external APIs and store them in DB';

    public function handle()
    {
        $this->info('Fetching articles...');

        $sources = [
            'NewsAPI' => NewsApiService::class,
            'Guardian' => GuardianService::class,
            'NYTimes' => NewYorkTimesService::class,
        ];

        foreach ($sources as $name => $serviceClass) {
            try {
                (new $serviceClass())->fetchArticles();
                $this->info("$name articles fetched ");
            } catch (\Exception $e) {
                // Log error to storage/logs/laravel.log
                Log::error("Failed to fetch $name articles: " . $e->getMessage());
                $this->error("Failed to fetch $name articles. Check logs for details.");
            }
        }

        $this->info('All sources processed.');
        return Command::SUCCESS;
    }
}
