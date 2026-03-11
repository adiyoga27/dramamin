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
        <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="aspect-[2/3] overflow-hidden">
                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                    <a href="{{ route('admin.movies.show', $movie) }}" class="w-full py-2 bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-center text-sm font-semibold rounded-xl border border-white/30 transition-all">
                        View Episodes
                    </a>
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-sm line-clamp-2 h-10 group-hover:text-blue-600 transition-colors">{{ $movie->title }}</h3>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                    <span class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg>
                        {{ $movie->episodes_count }} EP
                    </span>
                    @php
                        $badgeColors = [
                            'Dramabox' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
                            'Melolo' => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
                            'Netshort' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
                            'Reellife' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
                        ];
                        $colorClass = $badgeColors[$movie->resource->name] ?? 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
                    @endphp
                    <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider {{ $colorClass }}">
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
