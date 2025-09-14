<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{

    use HasFactory;
    protected $fillable = [
        'source_id',
        'category_id',
        'author_id',
        'external_id',
        'title',
        'slug',
        'description',
        'content',
        'url',
        'image_url',
        'language',
        'published_at',
        'raw'
    ];


    protected $casts = [
        'raw' => 'array',
        'published_at' => 'datetime',
    ];


    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
