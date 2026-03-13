@extends('layouts.admin')

@section('title', 'API Documentation')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header -->
    <div class="glass dark:glass-dark rounded-3xl p-8 mb-8 border border-gray-100 dark:border-gray-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-4">API Documentation</h2>
                <p class="text-gray-600 dark:text-gray-300 text-lg">
                    Access our powerful API to integrate movie data and episodes into your own applications.
                </p>
            </div>
            <a href="/docs/api" target="_blank" class="btn-primary inline-flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Open Interactive Playground
            </a>
        </div>
    </div>

    <!-- Token Management -->
    <div class="glass dark:glass-dark rounded-3xl p-8 mb-8 border border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                Personal Access Token
            </h3>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Hits</p>
                    <p class="text-lg font-bold text-blue-600">{{ number_format($token_hits) }}</p>
                </div>
                @if($last_used_at)
                <div class="text-right border-l pl-4 border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Last Used</p>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $last_used_at->diffForHumans() }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($token)
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl">
                <p class="text-sm text-blue-700 dark:text-blue-300 font-semibold mb-2">New Token Generated:</p>
                <div class="flex items-center space-x-2">
                    <code class="flex-1 bg-white dark:bg-gray-900 px-4 py-2 rounded-xl border border-blue-200 dark:border-blue-700 text-blue-600 font-mono break-all">{{ $token }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ $token }}')" class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-800 rounded-lg transition-colors" title="Copy to clipboard">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    </button>
                </div>
                <p class="mt-2 text-xs text-blue-600/70">Warning: For security, this token will only be shown once. Please store it safely.</p>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Generate a token to authenticate your requests. Generating a new token will revoke any existing token for your account.
            </div>
            <form action="{{ route('admin.docs.api.token') }}" method="POST">
                @csrf
                <button type="submit" class="btn-secondary w-full sm:w-auto flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    {{ $token_hits > 0 || $last_used_at ? 'Regenerate Token' : 'Generate Token' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Reference Header -->
    <div class="flex items-center space-x-4 mb-6 px-4">
        <h3 class="text-xl font-bold">Quick Reference</h3>
        <div class="h-px flex-1 bg-gray-100 dark:bg-gray-800"></div>
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
