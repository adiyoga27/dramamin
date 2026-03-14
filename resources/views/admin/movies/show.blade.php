@extends('layouts.admin')

@section('title', $movie->title)

@section('content')
<div class="space-y-6">
    <!-- Movie Info -->
    <div class="glass p-8 rounded-3xl flex flex-col md:flex-row gap-8 shadow-sm">
        <div class="w-full md:w-64 flex-shrink-0">
            <img src="{{ $movie->poster_url }}" class="w-full aspect-[2/3] object-cover rounded-2xl shadow-xl shadow-gray-200 dark:shadow-none">
        </div>
        <div class="flex-1 space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-full uppercase tracking-wider">
                    {{ $movie->resource->name }}
                </span>
                <span class="text-gray-400 text-sm">•</span>
                <span class="text-sm font-medium">{{ $movie->episodes->count() }} Episodes</span>
            </div>
            <h2 class="text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-400 bg-clip-text text-transparent italic">
                {{ $movie->title }}
            </h2>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-lg max-w-3xl">
                {{ $movie->description }}
            </p>

            @if($movie->tags || $movie->play_count || $movie->shelf_time)
            <div class="space-y-3 pt-2">
                @if($movie->tags && is_array($movie->tags))
                <div class="flex flex-wrap gap-2">
                    @foreach($movie->tags as $tag)
                        <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-lg">{{ is_string($tag) ? $tag : ($tag['tagName'] ?? '') }}</span>
                    @endforeach
                </div>
                @endif
                <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                    @if($movie->chapter_count)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span>{{ $movie->chapter_count }} Chapters</span>
                        </div>
                    @endif
                    @if($movie->play_count)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span>{{ $movie->play_count }} Plays</span>
                        </div>
                    @endif
                    @if($movie->shelf_time)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Listed {{ $movie->shelf_time->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </div>
            @endif
            <div class="flex flex-wrap gap-4 pt-4">
                <a href="{{ route('admin.movies.play', $movie) }}" class="btn-primary flex items-center bg-indigo-600 border-indigo-600 hover:bg-indigo-700 hover:border-indigo-700 w-full md:w-auto">
                    <svg class="w-5 h-5 mr-3" fill="solid" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Play All
                </a>

                <form action="{{ route('admin.episodes.sync', $movie) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                        Refresh Episodes
                    </button>
                </form>

                <div x-data="downloadTracker" 
                     data-movie-id="{{ $movie->id }}" 
                     data-is-downloading="{{ $isDownloading ? 'true' : 'false' }}"
                     data-completed="{{ $completedEpisodes }}"
                     data-total="{{ $totalEpisodes }}"
                     class="w-full md:w-auto">
                    <template x-if="!isDownloading">
                        <form @submit.prevent="startDownload" action="{{ route('admin.episodes.downloadAll', $movie) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary flex items-center bg-emerald-600 border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700 w-full md:w-auto" onclick="return confirm('This will bulk download all un-downloaded episodes to storage in the background. Continue?');">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5m-5 5V3"></path></svg>
                                Download All
                            </button>
                        </form>
                    </template>
                    <template x-if="isDownloading">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4 border border-blue-100 dark:border-blue-800 w-full min-w-[280px]">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-bold text-blue-700 dark:text-blue-300">Downloading Episodes...</span>
                                <span class="text-blue-600 dark:text-blue-400 font-black font-mono" x-text="`${completed} / ${total}`"></span>
                            </div>
                            <div class="w-full bg-blue-100 dark:bg-blue-900/50 rounded-full h-3 overflow-hidden border border-blue-200 dark:border-blue-800">
                                <div class="bg-blue-600 h-full rounded-full transition-all duration-700 ease-out shadow-[0_0_10px_rgba(37,99,235,0.5)]" :style="`width: ${percentage}%`"></div>
                            </div>
                            <p class="mt-2 text-[10px] text-blue-500 uppercase font-bold tracking-widest text-center" x-text="`${percentage}% Complete`"></p>
                        </div>
                    </template>
                </div>

                <a href="{{ route('admin.movies.export', $movie) }}" class="btn-secondary flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download API Data
                </a>

                <a href="{{ route('admin.movies.index') }}" class="btn-secondary flex items-center bg-gray-50 dark:bg-gray-800">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Episode List -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-xl flex items-center">
                <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Episodes
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50 dark:bg-gray-700/50 text-gray-500 text-xs font-bold uppercase tracking-widest">
                    <tr>
                        <th class="px-8 py-4">Title</th>
                        <th class="px-8 py-4">Status</th>
                        <th class="px-8 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($episodes as $episode)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-8 py-5">
                            <span class="font-bold text-gray-900 dark:text-white">{{ $episode->title }}</span>
                        </td>
                        <td class="px-8 py-5">
                            @if($episode->status === 'completed')
                                <span class="px-3 py-1 bg-green-100 text-green-600 text-xs font-bold rounded-full">Downloaded</span>
                            @elseif($episode->status === 'downloading')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-600 text-xs font-bold rounded-full animate-pulse">Downloading...</span>
                            @elseif($episode->status === 'failed')
                                <span class="px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full">Failed</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-500 text-xs font-bold rounded-full">Pending</span>
                            @endif
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.episodes.export', $episode) }}" class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="Export JSON">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                                @if($episode->status === 'completed' && $episode->local_path)
                                <a href="{{ route('admin.movies.play', ['movie' => $movie, 'episode' => $episode->id]) }}" class="p-2 text-indigo-500 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-xl transition-colors" title="Play Episode">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </a>
                                @endif

                                <form action="{{ route('admin.episodes.download', $episode) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 {{ $episode->local_path ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white shadow-sm' }} font-bold text-sm rounded-xl transition-all"
                                        {{ $episode->local_path ? 'disabled' : '' }}>
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ $episode->local_path ? 'Offline' : 'Download' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-8 py-12 text-center text-gray-500">No episodes synced for this movie.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $episodes->links() }}
        </div>
    </div>
</div>
@endsection
