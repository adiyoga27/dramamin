@extends('layouts.admin')

@section('title', 'Playing: ' . $currentEpisode->title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-400 bg-clip-text text-transparent">
                {{ $movie->title }}
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Now Playing: <span class="font-bold text-gray-900 dark:text-white">{{ $currentEpisode->title }}</span>
            </p>
        </div>
        <a href="{{ route('admin.movies.show', $movie) }}" class="btn-secondary flex items-center bg-white dark:bg-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Movie
        </a>
    </div>

    <!-- Player Area -->
    <div class="flex flex-col xl:flex-row gap-6">
        
        <!-- Main Video -->
        <div class="flex-1">
            <div class="bg-black rounded-3xl overflow-hidden shadow-2xl relative aspect-video">
                <video 
                    id="videoPlayer" 
                    class="w-full h-full" 
                    controls 
                    autoplay
                    src="{{ asset('storage/' . $currentEpisode->local_path) }}">
                    Your browser does not support the video tag.
                </video>
            </div>
            
            <!-- Custom Player Controls below video -->
            <div class="mt-4 glass p-4 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Playback Speed:</span>
                    <select id="speedSelect" class="rounded-xl border-gray-200 py-1.5 px-3 bg-white/50 backdrop-blur focus:ring-blue-500 text-sm font-bold">
                        <option value="0.5">0.5x</option>
                        <option value="1" selected>1.0x (Normal)</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.5">1.5x</option>
                        <option value="2">2.0x</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-3">
                    @if($nextEpisode)
                        <span class="text-sm text-gray-500">Up Next: <span class="font-bold">{{ $nextEpisode->title }}</span></span>
                        <a href="{{ route('admin.movies.play', ['movie' => $movie, 'episode' => $nextEpisode->id]) }}" class="btn-primary text-sm py-2 px-4 flex items-center gap-2">
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                        </a>
                    @else
                        <span class="text-sm font-medium text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg">End of Playlist</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Playlist Sidebar -->
        <div class="w-full xl:w-80 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm h-full max-h-[600px] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-lg flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                        Playlist
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $episodes->count() }} downloaded episodes</p>
                </div>
                
                <div class="overflow-y-auto flex-1 p-2 space-y-1">
                    @foreach($episodes as $ep)
                        <a href="{{ route('admin.movies.play', ['movie' => $movie, 'episode' => $ep->id]) }}" 
                           class="block px-4 py-3 rounded-xl transition-all {{ $currentEpisode->id === $ep->id ? 'bg-indigo-50 border border-indigo-100 dark:bg-indigo-900/30 dark:border-indigo-800' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50 border border-transparent' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-sm {{ $currentEpisode->id === $ep->id ? 'text-indigo-700 dark:text-indigo-400 font-bold' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $ep->title }}
                                </span>
                                @if($currentEpisode->id === $ep->id)
                                    <span class="flex h-3 w-3 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('videoPlayer');
        const speedSelect = document.getElementById('speedSelect');
        const nextUrl = "{{ $nextEpisode ? route('admin.movies.play', ['movie' => $movie, 'episode' => $nextEpisode->id]) : '' }}";

        // Handle playback speed changes
        speedSelect.addEventListener('change', function() {
            video.playbackRate = parseFloat(this.value);
            // Save preference to localStorage
            localStorage.setItem('dracin_playback_speed', this.value);
        });

        // Restore previous speed preference if available
        const savedSpeed = localStorage.getItem('dracin_playback_speed');
        if (savedSpeed) {
            speedSelect.value = savedSpeed;
            video.playbackRate = parseFloat(savedSpeed);
        }

        // Try to force play for reliable autoplay
        const playPromise = video.play();
        if (playPromise !== undefined) {
            playPromise.catch(error => {
                console.log("Autoplay prevented by browser policy.");
            });
        }

        // Auto-play next episode
        video.addEventListener('ended', function() {
            if (nextUrl) {
                // Little delay before redirecting
                setTimeout(() => {
                    window.location.href = nextUrl;
                }, 1000);
            }
        });
    });
</script>
@endpush
@endsection
