<?php

namespace Tests\Feature;

use App\Jobs\DownloadEpisodeJob;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadEpisodeJobTest extends TestCase
{

    public function test_it_downloads_episode_and_updates_status(): void
    {
        Storage::fake('public');

        Http::fake([
            'https://example.com/video.mp4' => Http::response('fake video content', 200),
        ]);

        $resource = Resource::create([
            'name' => 'Dramabox',
            'api_url' => 'https://example.com/api/',
        ]);

        $movie = Movie::create([
            'resource_id' => $resource->id,
            'external_id' => 'movie123',
            'title' => 'Test Movie',
        ]);

        $episode = Episode::create([
            'movie_id' => $movie->id,
            'external_id' => 'ep123',
            'title' => 'Test Episode',
            'download_url' => 'https://example.com/video.mp4',
            'status' => 'pending',
        ]);

        $job = new DownloadEpisodeJob($episode);
        $job->handle();

        $episode->refresh();

        $this->assertEquals('completed', $episode->status);
        $this->assertEquals("episodes/{$movie->id}/episode_ep123.mp4", $episode->local_path);
        Storage::disk('public')->assertExists($episode->local_path);
    }
}
