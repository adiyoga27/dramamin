<?php

namespace Tests\Feature;

use App\Jobs\DownloadEpisodeJob;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Resource;
use App\Services\MovieApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncDramaBoxCommandTest extends TestCase
{

    public function test_it_syncs_movies_and_dispatches_jobs(): void
    {
        Queue::fake();

        $resource = Resource::create([
            'name' => 'Dramabox',
            'api_url' => 'https://example.com/api/',
        ]);

        $movie = Movie::create([
            'resource_id' => $resource->id,
            'external_id' => 'movie123',
            'title' => 'Test Movie',
        ]);

        Episode::create([
            'movie_id' => $movie->id,
            'external_id' => 'ep1',
            'title' => 'Episode 1',
            'download_url' => 'https://example.com/video1.mp4',
            'status' => 'pending',
        ]);

        $this->mock(MovieApiService::class, function (MockInterface $mock) {
            $mock->shouldReceive('syncMovies')->once()->andReturn(1);
            $mock->shouldReceive('syncEpisodes')->once()->andReturn(1);
        });

        $this->artisan('dramabox:sync')
            ->assertExitCode(0);

        Queue::assertPushed(DownloadEpisodeJob::class, function ($job) {
            return $job->episode->external_id === 'ep1';
        });
    }
}
