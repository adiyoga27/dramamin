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

        // In a real app, this should be a background job.
        // For this demo, we'll do it synchronously or simulate it.

        try {
            $episode->update(['status' => 'downloading']);

            $url = $episode->download_url;
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp4';
            $filename = "videos/{$episode->movie->id}/{$episode->external_id}.{$extension}";

            $response = Http::get($url);

            if ($response->successful()) {
                Storage::disk('public')->put($filename, $response->body());
                $episode->update([
                    'local_path' => $filename,
                    'status' => 'completed',
                ]);

                return back()->with('success', 'Download completed.');
            } else {
                $episode->update(['status' => 'failed']);

                return back()->with('error', 'Download failed: API error.');
            }
        } catch (\Exception $e) {
            $episode->update(['status' => 'failed']);

            return back()->with('error', 'Download failed: '.$e->getMessage());
        }
    }

    public function downloadAll(Movie $movie)
    {
        $episodes = $movie->episodes()->where('status', '!=', 'completed')->get();

        if ($episodes->isEmpty()) {
            return back()->with('info', 'All episodes are already downloaded.');
        }

        foreach ($episodes as $episode) {
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
