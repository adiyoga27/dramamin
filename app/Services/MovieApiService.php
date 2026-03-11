<?php

namespace App\Services;

use App\Models\Resource;
use App\Models\Movie;
use App\Models\Episode;
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
            // Determine the URL based on the resource
            $url = $resource->api_url;
            if ($resource->name === 'Dramabox') {
                $url .= 'latest?lang=in';
            } elseif ($resource->name === 'Melolo') {
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

                // Parse the response based on the resource name
                switch ($resource->name) {
                    case 'Dramabox':
                        $moviesData = $responseData['recommendList']['records'] ?? $responseData;
                        break;
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

                $count = 0;
                foreach ($moviesData as $movieData) {
                    // Standardize field extraction based on source differences
                    $externalId = $movieData['bookId'] ?? $movieData['shortPlayId'] ?? null;
                    $title = $movieData['bookName'] ?? $movieData['shortPlayName'] ?? 'Unknown Title';
                    $coverUrl = $movieData['coverWap'] ?? $movieData['shortPlayCover'] ?? $movieData['thumb_url'] ?? null;
                    $description = $movieData['introduction'] ?? $movieData['abstract'] ?? null;

                    if ($externalId) {
                        Movie::updateOrCreate(
                            [
                                'resource_id' => $resource->id,
                                'external_id' => $externalId
                            ],
                            [
                                'title' => $title,
                                'poster_url' => $coverUrl,
                                'description' => $description,
                                'metadata' => $movieData,
                                'last_sync_at' => now(),
                            ]
                        );
                        $count++;
                    }
                }

                return $count;
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync movies for resource {$resource->name}: " . $e->getMessage());
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
            // As per user instruction, all episodes use this Dramabox endpoint
            $url = 'https://dramabox.dramabos.my.id/api/v1/allepisode';
            $apiKey = config('services.dramabos.api_key');
            
            $response = Http::get($url, [
                'bookId' => $movie->external_id,
                'code' => $apiKey
            ]);

            if ($response->successful()) {
                $episodes = $response->json();

                foreach ($episodes as $episodeData) {
                    Episode::updateOrCreate(
                        [
                            'movie_id' => $movie->id,
                            'external_id' => $episodeData['chapterId']
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
            Log::error("Failed to sync episodes for movie {$movie->title}: " . $e->getMessage());
        }

        return 0;
    }
}
