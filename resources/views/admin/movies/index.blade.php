@extends('layouts.admin')

@section('title', 'Movies Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search -->
            <form action="{{ route('admin.movies.index') }}" method="GET" class="relative group w-full md:w-96">
                <!-- Preserve resource_id if filtering -->
                @if(request('resource_id'))
                    <input type="hidden" name="resource_id" value="{{ request('resource_id') }}">
                @endif
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search movies..." class="w-full pl-10 pr-4 py-3 rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 glass transition-all">
            </form>

            <div class="flex flex-wrap items-center gap-4">
                <form action="{{ route('admin.movies.sync') }}" method="POST" class="flex gap-2 items-center">
                    @csrf
                    <select name="resource_id" required class="rounded-xl border-gray-200 py-2.5 px-3 bg-white/50 backdrop-blur focus:ring-blue-500 text-sm">
                        <option value="" disabled {{ !request('resource_id') ? 'selected' : '' }}>Select Source...</option>
                        @foreach($resources as $resource)
                            <option value="{{ $resource->id }}" {{ request('resource_id') == $resource->id ? 'selected' : '' }}>{{ $resource->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary flex items-center h-full">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Sync
                    </button>
                </form>
                
                <a href="#" onclick="alert('Exporting feature available per movie for now. Click on a movie to export its API data.'); return false;" class="btn-secondary flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download API
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.movies.index', array_merge(request()->except('resource_id', 'page'))) }}" 
               class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors border {{ !request('resource_id') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white/50 text-gray-600 border-gray-200 hover:bg-white' }}">
                All Sources
            </a>
            @foreach($resources as $resource)
                <a href="{{ route('admin.movies.index', array_merge(request()->except('page'), ['resource_id' => $resource->id])) }}" 
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors border {{ request('resource_id') == $resource->id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white/50 text-gray-600 border-gray-200 hover:bg-white' }}">
                    {{ $resource->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Movie Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
        @forelse($movies as $movie)
        <div x-data="downloadTracker" 
             data-movie-id="{{ $movie->id }}" 
             data-is-downloading="{{ $movie->in_progress_count > 0 ? 'true' : 'false' }}"
             data-completed="{{ $movie->downloaded_count }}"
             data-total="{{ $movie->episodes_count }}"
             class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            
            <div class="aspect-[2/3] overflow-hidden relative">
                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                
                <!-- Status Badge -->
                <div class="absolute top-2 left-2 z-10 flex flex-col gap-2">
                    <template x-if="completed > 0">
                        <span class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-500/90 backdrop-blur text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-lg border border-white/20">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            Offline
                            <span class="opacity-75 font-medium ml-0.5" x-text="'(' + completed + '/' + total + ')'"></span>
                        </span>
                    </template>
                    
                    <template x-if="isDownloading">
                        <span class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-500/90 backdrop-blur text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-lg border border-white/20">
                            <svg class="animate-spin h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Syncing...
                        </span>
                    </template>
                </div>

                <!-- Progress Overlay (Only when downloading) -->
                <template x-if="isDownloading">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] flex flex-col items-center justify-center p-4 z-20">
                        <div class="w-full max-w-[80px] aspect-square relative flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="40" cy="40" r="32" stroke="currentColor" stroke-width="6" fill="transparent" class="text-white/20" />
                                <circle cx="40" cy="40" r="32" stroke="currentColor" stroke-width="6" fill="transparent" class="text-blue-500 transition-all duration-500" 
                                        stroke-dasharray="201" 
                                        :stroke-dashoffset="201 - (201 * percentage / 100)" />
                            </svg>
                            <span class="absolute text-white text-xs font-black" x-text="percentage + '%'"></span>
                        </div>
                    </div>
                </template>

                <!-- Actions Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4 z-30 space-y-2">
                    <template x-if="!isDownloading && completed < total">
                        <button @click.prevent="startDownload" class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-center text-xs font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download All
                        </button>
                    </template>
                    <a href="{{ route('admin.movies.show', $movie) }}" class="w-full py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white text-center text-[10px] font-bold uppercase tracking-wider rounded-xl border border-white/20 transition-all">
                        Episode List
                    </a>
                </div>
            </div>
            
            <div class="p-4 bg-white dark:bg-gray-800">
                <h3 class="font-bold text-sm line-clamp-2 h-10 group-hover:text-blue-600 transition-colors">{{ $movie->title }}</h3>
                <div class="mt-2 flex items-center justify-between text-[10px] font-bold text-gray-400">
                    <span class="flex items-center uppercase tracking-widest font-black">
                        <svg class="w-3 h-3 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg>
                        <span x-text="total"></span> EPISODES
                    </span>
                    @php
                        $badgeColors = [
                            'Dramabox' => 'bg-red-500/10 text-red-600 border-red-500/20',
                            'Melolo' => 'bg-purple-500/10 text-purple-600 border-purple-500/20',
                            'Netshort' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                            'Reellife' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                        ];
                        $colorClass = $badgeColors[$movie->resource->name] ?? 'bg-gray-500/10 text-gray-600 border-gray-500/20';
                    @endphp
                    <span class="px-2 py-0.5 rounded border text-[8px] uppercase tracking-tighter {{ $colorClass }}">
                        {{ $movie->resource->name }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center glass rounded-2xl">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <p class="text-gray-500 font-medium">No movies found. Try syncing or searching something else.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $movies->links() }}
    </div>
</div>
@endsection
