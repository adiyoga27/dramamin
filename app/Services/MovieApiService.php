<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\Resource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MovieApiService
{
    /**
     * Sync movies from the external API for a given resource.
     */
    public function syncMovies(Resource $resource)
    {
        try {
            $count = 0;

            if ($resource->name === 'Dramabox') {
                for ($page = 1; $page <= 30; $page++) {
                    $url = $resource->api_url."homepage?page={$page}&lang=in";
                    $response = Http::get($url);

                    if ($response->successful()) {
                        $responseData = $response->json();
                        $moviesData = $responseData['recommendList']['records'] ?? [];

                        if (empty($moviesData)) {
                            break; // Stop if there are no more records
                        }

                        foreach ($moviesData as $movieData) {
                            $externalId = $movieData['bookId'] ?? null;
                            $title = $movieData['bookName'] ?? 'Unknown Title';
                            $coverUrl = $movieData['coverWap'] ?? null;
                            $description = $movieData['introduction'] ?? null;
                            $chapterCount = $movieData['chapterCount'] ?? null;
                            $tags = $movieData['tags'] ?? [];
                            $playCount = $movieData['playCount'] ?? null;
                            $shelfTime = $movieData['shelfTime'] ?? null;

                            if ($externalId) {
                                $movie = Movie::updateOrCreate(
                                    [
                                        'resource_id' => $resource->id,
                                        'external_id' => $externalId,
                                    ],
                                    [
                                        'title' => $title,
                                        'poster_url' => $coverUrl,
                                        'description' => $description,
                                        'chapter_count' => $chapterCount,
                                        'tags' => $tags,
                                        'play_count' => $playCount,
                                        'shelf_time' => $shelfTime,
                                        'metadata' => $movieData,
                                        'last_sync_at' => now(),
                                    ]
                                );

                                // Automatically sync episodes for this movie
                                $this->syncEpisodes($movie);

                                $count++;
                            }
                        }
                    } else {
                        break; // Stop on API error
                    }
                }
            } elseif ($resource->name === 'Reelshort') {
                $tabs = ['NEW', 'POPULER', 'ASIA', 'RANKING', 'KHUSUS PRIA', 'KHUSUS WANITA'];

                foreach ($tabs as $tab) {
                    $tabUrl = $resource->api_url.'home?tab='.urlencode($tab).'&lang=id';
                    $response = Http::withOptions(['verify' => false])->get($tabUrl);

                    if (! $response->successful()) {
                        continue;
                    }

                    $books = $response->json()['books'] ?? [];

                    foreach ($books as $book) {
                        $externalId = $book['id'] ?? null;
                        $title = $book['title'] ?? 'Unknown Title';
                        $coverUrl = $book['pic'] ?? null;
                        $chapterCount = $book['chapters'] ?? null;

                        if (! $externalId) {
                            continue;
                        }

                        $description = null;
                        $tags = [];
                        $playCount = null;

                        $detailUrl = $resource->api_url.'detail/'.$externalId.'?lang=id';
                        $detailResponse = Http::withOptions(['verify' => false])->get($detailUrl);

                        if ($detailResponse->successful()) {
                            $detail = $detailResponse->json();
                            $description = $detail['desc'] ?? null;
                            $tags = $detail['theme'] ?? [];
                            $playCount = $detail['views'] ?? null;
                        }

                        $movie = Movie::updateOrCreate(
                            [
                                'resource_id' => $resource->id,
                                'external_id' => $externalId,
                            ],
                            [
                                'title' => $title,
                                'poster_url' => $coverUrl,
                                'description' => $description,
                                'chapter_count' => $chapterCount,
                                'tags' => $tags,
                                'play_count' => (string) $playCount,
                                'shelf_time' => now(),
                                'metadata' => array_merge($book, $detail ?? []),
                                'last_sync_at' => now(),
                            ]
                        );

                        $this->syncEpisodes($movie);

                        $count++;
                    }
                }
            } else {
                // Handling for other resources
                $url = $resource->api_url;
                if ($resource->name === 'Melolo') {
                    $url .= 'home?lang=id&offset=0';
                } elseif ($resource->name === 'Netshort') {
                    $url .= 'list/1?lang=in';
                } elseif ($resource->name === 'Reellife') {
                    $url .= 'home?page=1&lang=in';
                }

                $response = Http::get($url);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $moviesData = [];

                    switch ($resource->name) {
                        case 'Melolo':
                            $moviesData = $responseData['data']['cell']['books'] ?? [];
                            break;
                        case 'Netshort':
                            $moviesData = $responseData['data']['dataList'] ?? [];
                            break;
                        case 'Reellife':
                            $moviesData = $responseData['data']['dramas'] ?? [];
                            break;
                    }

                    foreach ($moviesData as $movieData) {
                        $externalId = $movieData['bookId'] ?? $movieData['shortPlayId'] ?? null;
                        $title = $movieData['bookName'] ?? $movieData['shortPlayName'] ?? 'Unknown Title';
                        $coverUrl = $movieData['coverWap'] ?? $movieData['shortPlayCover'] ?? $movieData['thumb_url'] ?? null;
                        $description = $movieData['introduction'] ?? $movieData['abstract'] ?? null;

                        if ($externalId) {
                            $movie = Movie::updateOrCreate(
                                [
                                    'resource_id' => $resource->id,
                                    'external_id' => $externalId,
                                ],
                                [
                                    'title' => $title,
                                    'poster_url' => $coverUrl,
                                    'description' => $description,
                                    'metadata' => $movieData,
                                    'last_sync_at' => now(),
                                ]
                            );

                            // Automatically sync episodes for this movie
                            $this->syncEpisodes($movie);

                            $count++;
                        }
                    }
                }
            }

            return $count;
        } catch (\Exception $e) {
            Log::error("Failed to sync movies for resource {$resource->name}: ".$e->getMessage());
            dd($e->getMessage()); // Temporary for debugging if needed
        }

        return 0;
    }

    /**
     * Sync episodes for a given movie.
     */
    public function syncEpisodes(Movie $movie)
    {
        try {
            $resource = $movie->resource;

            if ($resource && $resource->name === 'Reelshort') {
                return $this->syncReelshortEpisodes($movie, $resource);
            }

            $url = 'https://dramabox.dramabos.my.id/api/v1/allepisode';
            $apiKey = config('services.dramabos.api_key');

            $response = Http::withOptions(['verify' => false])->get($url, [
                'bookId' => $movie->external_id,
                'code' => $apiKey,
            ]);

            if ($response->successful()) {
                $episodes = $response->json();

                foreach ($episodes as $episodeData) {
                    Episode::updateOrCreate(
                        [
                            'movie_id' => $movie->id,
                            'external_id' => $episodeData['chapterId'],
                        ],
                        [
                            'title' => $episodeData['chapterName'],
                            'download_url' => $episodeData['videoUrl'],
                        ]
                    );
                }

                return count($episodes);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync episodes for movie {$movie->title}: ".$e->getMessage());
        }

        return 0;
    }

    private function syncReelshortEpisodes(Movie $movie, Resource $resource)
    {
        $url = $resource->api_url.'allepisodes/'.$movie->external_id;
        $code = $resource->api_key ?: config('services.dramabos.api_key');

        $response = Http::withOptions(['verify' => false])->get($url, [
            'code' => $code,
        ]);

        if (! $response->successful()) {
            return 0;
        }

        $data = $response->json();
        $episodes = $data['episodes'] ?? [];

        foreach ($episodes as $ep) {
            $streams = $ep['streams'] ?? [];
            $bestStream = collect($streams)->firstWhere('quality', '720p');
            if (! $bestStream) {
                $bestStream = $streams[0] ?? null;
            }

            Episode::updateOrCreate(
                [
                    'movie_id' => $movie->id,
                    'external_id' => $ep['id'],
                ],
                [
                    'title' => $ep['name'],
                    'download_url' => $bestStream['url'] ?? null,
                ]
            );
        }

        return count($episodes);
    }
}
