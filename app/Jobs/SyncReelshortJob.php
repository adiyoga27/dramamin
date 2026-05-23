<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\Resource;
use App\Services\MovieApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncReelshortJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 600;

    public function __construct(public Resource $resource) {}

    public function handle(MovieApiService $apiService): void
    {
        $count = $apiService->syncMovies($this->resource);

        $movies = Movie::where('resource_id', $this->resource->id)->get();

        $episodesToDownload = Episode::whereIn('movie_id', $movies->pluck('id'))
            ->whereIn('status', ['pending', 'failed'])
            ->whereNotNull('download_url')
            ->get();

        foreach ($episodesToDownload as $episode) {
            DownloadEpisodeJob::dispatch($episode);
        }

        Log::info("SyncReelshortJob: Synced {$count} movies, dispatched ".$episodesToDownload->count().' download jobs.');
    }
}
