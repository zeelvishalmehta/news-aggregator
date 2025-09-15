<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Cache; // <-- add this

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List articles with optional filters, search, and pagination
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'source' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
                'author' => 'sometimes|string|max:255',
                'date_from' => 'sometimes|date',
                'date_to' => 'sometimes|date',
                'q' => 'sometimes|string|max:255',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->errors(),
            ], 422);
        }

        $perPage = (int) $request->get('per_page', 10);

        // Build unique cache key based on user + filters
        $cacheKey = 'articles_' . auth()->id() . '_' . md5(json_encode($request->all())) . "_page_" . $request->get('page', 1);

        $articles = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request, $perPage) {
            $query = Article::query()->with(['author', 'category', 'source']);

            if ($request->filled('source')) {
                $query->whereHas('source', function ($q) use ($request) {
                    $q->where('slug', $request->source)
                      ->orWhere('name', 'like', "%{$request->source}%");
                });
            }

            if ($request->filled('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category)
                      ->orWhere('name', 'like', "%{$request->category}%");
                });
            }

            if ($request->filled('author')) {
                $query->whereHas('author', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->author}%");
                });
            }

            if ($request->filled('date_from')) {
                $from = Carbon::parse($request->date_from)->startOfDay();
                $query->where('published_at', '>=', $from);
            }

            if ($request->filled('date_to')) {
                $to = Carbon::parse($request->date_to)->endOfDay();
                $query->where('published_at', '<=', $to);
            }

            if ($request->filled('q')) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            if (auth()->check()) {
                $prefs = UserPreference::where('user_id', auth()->id())->first();

                if ($prefs) {
                    if (!empty($prefs->preferred_sources)) {
                        $slugs = collect($prefs->preferred_sources)->map(fn($s) => "'$s'")->join(',');
                        $query->join('sources', 'articles.source_id', '=', 'sources.id')
                              ->orderByRaw("FIELD(sources.slug, $slugs) DESC");
                    }

                    if (!empty($prefs->preferred_categories)) {
                        $slugs = collect($prefs->preferred_categories)->map(fn($s) => "'$s'")->join(',');
                        $query->join('categories', 'articles.category_id', '=', 'categories.id')
                              ->orderByRaw("FIELD(categories.slug, $slugs) DESC");
                    }

                    if (!empty($prefs->preferred_authors)) {
                        $names = collect($prefs->preferred_authors)->map(fn($n) => "'$n'")->join(',');
                        $query->join('authors', 'articles.author_id', '=', 'authors.id')
                              ->orderByRaw("FIELD(authors.name, $names) DESC");
                    }
                }
            }

            return $query->orderBy('published_at', 'desc')->paginate($perPage);
        });

        return response()->json([
            'status' => 'success',
            'data' => $articles,
        ]);
    }

    /**
     * Show a single article
     */
    public function show($id)
    {
        $cacheKey = "article_$id";

        $article = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            return Article::with(['author', 'category', 'source'])->find($id);
        });

        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $article,
        ]);
    }
}
