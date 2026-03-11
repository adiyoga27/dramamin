<?php

namespace App\Jobs;

use App\Models\Episode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadEpisodeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Episode $episode) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->episode->download_url) {
            Log::error("DownloadEpisodeJob: No download URL for episode {$this->episode->id}");
            $this->episode->update(['status' => 'failed']);

            return;
        }

        try {
            $this->episode->update(['status' => 'downloading']);

            $directory = "episodes/{$this->episode->movie_id}";
            Storage::disk('public')->makeDirectory($directory);

            $filename = "episode_{$this->episode->external_id}.mp4";
            $relativePath = "{$directory}/{$filename}";
            $absolutePath = Storage::disk('public')->path($relativePath);

            $response = Http::withOptions([
                'sink' => $absolutePath,
            ])->get($this->episode->download_url);

            if ($response->successful()) {
                $this->episode->update([
                    'local_path' => $relativePath,
                    'status' => 'completed',
                ]);
            } else {
                Log::error("DownloadEpisodeJob: Failed to download episode {$this->episode->id}. HTTP status: ".$response->status());
                $this->episode->update(['status' => 'failed']);

                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
        } catch (\Exception $e) {
            Log::error("DownloadEpisodeJob: Exception during download for episode {$this->episode->id}: ".$e->getMessage());
            $this->episode->update(['status' => 'failed']);
        }
    }
}
