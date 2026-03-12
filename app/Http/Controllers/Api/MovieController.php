<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the movies.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 12);

        $movies = Movie::withCount(['episodes' => function ($query) {
            $query->where('status', 'completed');
        }])
            ->orderByDesc('id')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'per_page' => $movies->perPage(),
                'total' => $movies->total(),
            ],
        ]);
    }

    /**
     * Display the specified movie with available episodes.
     */
    public function show($id)
    {
        $movie = Movie::with(['episodes' => function ($query) {
            $query->where('status', 'completed')
                ->whereNotNull('local_path')
                ->orderBy('id', 'asc');
        }])->find($id);

        if (! $movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found',
            ], 404);
        }

        // Parse local_path to full URL for episodes
        $movie->episodes->transform(function ($episode) {
            $episode->download_url = $episode->local_path ? url('storage/'.$episode->local_path) : null;

            return $episode;
        });

        return response()->json([
            'success' => true,
            'data' => $movie,
        ]);
    }
}
