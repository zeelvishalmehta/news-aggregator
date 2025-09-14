<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GenerateUserToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage: php artisan token:generate {user_id}
     */
    protected $signature = 'token:generate {user_id}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a Sanctum API token for a given user ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }

        $token = $user->createToken('API Token')->plainTextToken;

        $this->info("API Token for user {$user->email}:");
        $this->line($token);

        return 0;
    }
}
