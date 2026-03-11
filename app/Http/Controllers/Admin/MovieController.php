<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Resource;
use App\Services\MovieApiService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected $apiService;

    public function __construct(MovieApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        $resources = Resource::all();
        $query = Movie::with('resource')->withCount('episodes');

        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('resource_id') && $request->resource_id != '') {
            $query->where('resource_id', $request->resource_id);
        }

        $movies = $query->latest('last_sync_at')->paginate(12);

        return view('admin.movies.index', compact('movies', 'resources'));
    }

    public function sync(Request $request)
    {
        $request->validate([
            'resource_id' => 'required|exists:resources,id'
        ]);

        $resource = Resource::find($request->resource_id);
        
        if (!$resource) {
            return back()->with('error', 'No resource found.');
        }

        $count = $this->apiService->syncMovies($resource);

        return back()->with('success', "Synced $count movies from {$resource->name} successfully.");
    }

    public function show(Movie $movie)
    {
        $episodes = $movie->episodes()->paginate(20);
        return view('admin.movies.show', compact('movie', 'episodes'));
    }

    public function exportJson(Movie $movie)
    {
        $data = $movie->load('episodes');
        $filename = "movie_{$movie->external_id}.json";
        
        return response($data->toJson(JSON_PRETTY_PRINT))
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
