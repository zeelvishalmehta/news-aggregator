<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPreference;

class PreferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET /api/preferences
    public function show(Request $request)
    {
        $prefs = UserPreference::where('user_id', $request->user()->id)->first();

        return response()->json([
            'status' => 'success',
            'data' => $prefs,
        ]);
    }

    // POST /api/preferences
    public function store(Request $request)
    {
        $validated = $request->validate([
            'preferred_sources' => 'nullable|array',
            'preferred_sources.*' => 'string',
            'preferred_categories' => 'nullable|array',
            'preferred_categories.*' => 'string',
            'preferred_authors' => 'nullable|array',
            'preferred_authors.*' => 'string',
        ]);

        $prefs = UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'preferred_sources' => $validated['preferred_sources'] ?? null,
                'preferred_categories' => $validated['preferred_categories'] ?? null,
                'preferred_authors' => $validated['preferred_authors'] ?? null,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Preferences saved',
            'data' => $prefs,
        ]);
    }

    
    public function destroy(Request $request)
    {
        UserPreference::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Preferences cleared',
        ]);
    }
}
