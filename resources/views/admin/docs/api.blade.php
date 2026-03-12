@extends('layouts.admin')

@section('title', 'API Documentation')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header -->
    <div class="glass dark:glass-dark rounded-3xl p-8 mb-8 border border-gray-100 dark:border-gray-800">
        <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-4">API Documentation</h2>
        <p class="text-gray-600 dark:text-gray-300 text-lg">
            This API allows third-party applications to retrieve the list of downloaded movies and their available episodes programmatically via JSON.
        </p>
    </div>

    <!-- Endpoint 1 -->
    <div class="glass dark:glass-dark rounded-3xl p-8 border border-gray-100 dark:border-gray-800">
        <div class="flex items-center space-x-4 mb-6">
            <span class="px-4 py-1.5 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-mono font-bold text-sm">GET</span>
            <code class="text-lg font-mono text-gray-800 dark:text-gray-200">/api/movies</code>
        </div>
        
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Returns a paginated list of all movies along with a count of their completely downloaded episodes.
        </p>

        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Query Parameters</h4>
        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mb-6 space-y-2">
            <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm">limit</code> (optional): Number of movies per page. Default 12.</li>
            <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm">page</code> (optional): Current page number.</li>
        </ul>

        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Example Response</h4>
        <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
<pre class="text-green-400 font-mono text-sm leading-relaxed">
{
  "success": true,
  "data": [
    {
      "id": 1,
      "resource_id": 1,
      "external_id": "42000006096",
      "title": "Asalku dari Zaman Kuno",
      "cover": "https://hwztchapter...",
      "chapter_count": 73,
      "tags": ["Perjalanan Waktu", "Modern"],
      "play_count": "5M",
      "episodes_count": 73
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 12,
    "total": 60
  }
}
</pre>
        </div>
    </div>

    <!-- Endpoint 2 -->
    <div class="glass dark:glass-dark rounded-3xl p-8 border border-gray-100 dark:border-gray-800">
        <div class="flex items-center space-x-4 mb-6">
            <span class="px-4 py-1.5 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-mono font-bold text-sm">GET</span>
            <code class="text-lg font-mono text-gray-800 dark:text-gray-200">/api/movies/{id}</code>
        </div>
        
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Returns the details of a specific movie. This endpoint fully eager-loads the actual <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">episodes</code> array of the movie but <strong>only includes episodes that have been successfully downloaded</strong> to the server. It also parses a ready-to-use <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm">download_url</code> pointing directly to your local storage.
        </p>

        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Example Response</h4>
        <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
<pre class="text-green-400 font-mono text-sm leading-relaxed">
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Asalku dari Zaman Kuno",
    "episodes": [
      {
        "id": 101,
        "movie_id": 1,
        "external_id": "ep_1",
        "title": "Episode 1",
        "status": "completed",
        "local_path": "episodes/1/episode_ep_1.mp4",
        "download_url": "https://yourdomain.com/storage/episodes/1/episode_ep_1.mp4"
      }
    ]
  }
}
</pre>
        </div>
    </div>

</div>
@endsection
