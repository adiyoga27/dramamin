@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass p-6 rounded-2xl shadow-sm">
            <p class="text-sm text-gray-500 font-medium">Resources</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['total_resources'] }}</p>
        </div>
        <div class="glass p-6 rounded-2xl shadow-sm">
            <p class="text-sm text-gray-500 font-medium">Total Movies</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['total_movies'] }}</p>
        </div>
        <div class="glass p-6 rounded-2xl shadow-sm">
            <p class="text-sm text-gray-500 font-medium">Synced Episodes</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['total_episodes'] }}</p>
        </div>
    </div>

    <!-- Recent Movies -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-lg">Recently Synced Movies</h3>
            <a href="{{ route('admin.movies.index') }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">Movie</th>
                        <th class="px-6 py-3">Resource</th>
                        <th class="px-6 py-3">Last Sync</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($stats['recent_movies'] as $movie)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="{{ $movie->poster_url }}" class="w-10 h-14 object-cover rounded-md mr-3 bg-gray-200">
                                <span class="font-medium">{{ $movie->title }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $movie->resource->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $movie->last_sync_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">No movies synced yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
