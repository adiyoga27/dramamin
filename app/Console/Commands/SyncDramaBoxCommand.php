<?php

namespace App\Console\Commands;

use App\Jobs\DownloadEpisodeJob;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Resource;
use App\Services\MovieApiService;
use Illuminate\Console\Command;

class SyncDramaBoxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dramabox:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync latest movies and episodes from Dramabox and download missing videos';

    /**
     * Execute the console command.
     */
    public function handle(MovieApiService $apiService)
    {
        $this->info('Starting Dramabox sync...');

        $resource = Resource::where('name', 'Dramabox')->first();

        if (! $resource) {
            $this->error('Dramabox resource not found.');

            return Command::FAILURE;
        }

        $this->info('Syncing movies...');
        $movieCount = $apiService->syncMovies($resource);
        $this->info("Synced {$movieCount} movies.");

        $movies = Movie::where('resource_id', $resource->id)->get();

        $totalEpisodesSynced = 0;
        foreach ($movies as $movie) {
            $this->info("Syncing episodes for movie: {$movie->title}");
            $totalEpisodesSynced += $apiService->syncEpisodes($movie);
        }
        $this->info("Finished syncing {$totalEpisodesSynced} episodes in total.");

        $this->info('Finding episodes to download...');
        $episodesToDownload = Episode::whereIn('movie_id', $movies->pluck('id'))
            ->whereIn('status', ['pending', 'failed'])
            ->whereNotNull('download_url')
            ->get();

        $this->info("Found {$episodesToDownload->count()} episodes to download.");

        foreach ($episodesToDownload as $episode) {
            DownloadEpisodeJob::dispatch($episode);
        }

        $this->info('Dramabox sync completed and jobs dispatched.');

        return Command::SUCCESS;
    }
}
