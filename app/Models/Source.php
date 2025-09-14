<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;
    protected $fillable = ['name','slug','api_name','api_key','base_url','meta','last_fetched_at'];
    protected $casts = ['meta' => 'array', 'last_fetched_at' => 'datetime'];

    public function articles(): HasMany {
        return $this->hasMany(Article::class);
    }
}
