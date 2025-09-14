<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

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
        // Validate query parameters
        try {
            $request->validate([
                'source'    => 'sometimes|string|max:255',
                'category'  => 'sometimes|string|max:255',
                'author'    => 'sometimes|string|max:255',
                'date_from' => 'sometimes|date',
                'date_to'   => 'sometimes|date',
                'q'         => 'sometimes|string|max:255',
                'per_page'  => 'sometimes|integer|min:1|max:100',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->errors(),
            ], 422);
        }

        $query = Article::query()->with(['author', 'category', 'source']);

        // Filter by source (slug or name)
        if ($request->filled('source')) {
            $query->whereHas('source', function ($q) use ($request) {
                $q->where('slug', $request->source)
                  ->orWhere('name', 'like', "%{$request->source}%");
            });
        }

        // Filter by category (slug or name)
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('name', 'like', "%{$request->category}%");
            });
        }

        // Filter by author name
        if ($request->filled('author')) {
            $query->whereHas('author', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->author}%");
            });
        }

        // Filter by published date range
        if ($request->filled('date_from')) {
            $from = Carbon::parse($request->date_from)->startOfDay();
            $query->where('published_at', '>=', $from);
        }

        if ($request->filled('date_to')) {
            $to = Carbon::parse($request->date_to)->endOfDay();
            $query->where('published_at', '<=', $to);
        }

        // Search in title, description, content
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = (int) $request->get('per_page', 10);
        $articles = $query->orderBy('published_at', 'desc')->paginate($perPage);

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
        $article = Article::with(['author', 'category', 'source'])->find($id);

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
