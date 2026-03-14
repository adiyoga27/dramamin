<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadEpisodeJob;
use App\Models\Episode;
use App\Models\Movie;
use App\Services\MovieApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EpisodeController extends Controller
{
    protected $apiService;

    public function __construct(MovieApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function sync(Movie $movie)
    {
        $count = $this->apiService->syncEpisodes($movie);

        return back()->with('success', "Synced $count episodes successfully.");
    }

    public function download(Episode $episode)
    {
        if ($episode->status === 'completed' && $episode->local_path) {
            return back()->with('info', 'Episode already downloaded.');
        }

        $episode->update(['status' => 'pending']);
        DownloadEpisodeJob::dispatch($episode);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Download started in background.',
            ]);
        }

        return back()->with('success', 'Download started in background.');
    }

    public function downloadAll(Movie $movie)
    {
        $episodes = $movie->episodes()->where('status', '!=', 'completed')->get();

        if ($episodes->isEmpty()) {
            return back()->with('info', 'All episodes are already downloaded.');
        }

        foreach ($episodes as $episode) {
            $episode->update(['status' => 'pending']);
            DownloadEpisodeJob::dispatch($episode);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Started background download for {$episodes->count()} episodes.",
                'count' => $episodes->count()
            ]);
        }

        return back()->with('success', "Started background download for {$episodes->count()} episodes.");
    }

    public function progress(Movie $movie)
    {
        $total = $movie->episodes()->whereNotNull('download_url')->count();
        $completed = $movie->episodes()->where('status', 'completed')->count();

        // Let's also find out if any are still pending/downloading to know if the batch is completely done
        $inProgress = $movie->episodes()->whereIn('status', ['pending', 'downloading'])->count();

        return response()->json([
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'is_finished' => $total > 0 && $inProgress === 0,
        ]);
    }

    public function exportJson(Episode $episode)
    {
        $filename = "episode_{$episode->external_id}.json";

        return response($episode->toJson(JSON_PRETTY_PRINT))
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
