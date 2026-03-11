<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Episode;
use App\Services\MovieApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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
                    'status' => 'completed'
                ]);
                return back()->with('success', 'Download completed.');
            } else {
                $episode->update(['status' => 'failed']);
                return back()->with('error', 'Download failed: API error.');
            }
        } catch (\Exception $e) {
            $episode->update(['status' => 'failed']);
            return back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }
    public function downloadAll(Movie $movie)
    {
        $episodes = $movie->episodes()->where('status', '!=', 'completed')->get();
        if ($episodes->isEmpty()) {
            return back()->with('info', 'All episodes are already downloaded.');
        }

        ob_end_clean(); // Clean output buffer to prevent issues with long synchronous requests if needed

        try {
            $downloaded = 0;
            foreach ($episodes as $episode) {
                $episode->update(['status' => 'downloading']);
                
                $url = $episode->download_url;
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp4';
                $filename = "videos/{$movie->id}/{$episode->external_id}.{$extension}";
                
                $response = Http::timeout(120)->get($url); // Add timeout for large files
                
                if ($response->successful()) {
                    Storage::disk('public')->put($filename, $response->body());
                    $episode->update([
                        'local_path' => $filename,
                        'status' => 'completed'
                    ]);
                    $downloaded++;
                } else {
                    $episode->update(['status' => 'failed']);
                }
            }

            return back()->with('success', "Successfully downloaded $downloaded episodes.");
        } catch (\Exception $e) {
            return back()->with('error', 'Batch download encountered an error: ' . $e->getMessage());
        }
    }
    public function exportJson(Episode $episode)
    {
        $filename = "episode_{$episode->external_id}.json";
        
        return response($episode->toJson(JSON_PRETTY_PRINT))
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
