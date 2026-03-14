<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN for UI without build step) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <style type="text/tailwindcss">
        @layer components {
            .glass {
                @apply bg-white/70 backdrop-blur-xl border border-white/20 shadow-xl;
            }
            .glass-dark {
                @apply bg-gray-900/70 backdrop-blur-xl border border-white/5 shadow-xl;
            }
            .btn-premium {
                @apply px-6 py-3 rounded-2xl font-bold transition-all duration-300 transform hover:-translate-y-1 active:scale-95 shadow-lg;
            }
            .btn-primary {
                @apply btn-premium bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-blue-500/25 hover:shadow-blue-500/40;
            }
            .btn-secondary {
                @apply btn-premium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-100 dark:border-gray-700 shadow-gray-200/50 hover:bg-gray-50;
            }
        }

        .text-gradient {
            @apply bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 hidden md:block">
            <div class="h-full flex flex-col">
                <div class="p-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        DracinAdmin
                    </a>
                </div>
                
                <nav class="flex-1 px-4 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.movies.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.movies.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Movies
                    </a>
                    <a href="{{ route('admin.docs.api') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.docs.api') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        API Docs
                    </a>
                </nav>

                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-xl transition-all">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md sticky top-0 z-30 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-xl font-semibold">@yield('title', 'Admin')</h1>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-green-50 text-green-600 border border-green-200 flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 rounded-xl bg-red-50 text-red-600 border border-red-200 flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    
    @stack('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('downloadTracker', function() {
                return {
                    isDownloading: false,
                    percentage: 0,
                    completed: 0,
                    total: 0,
                    movieId: null,
                    interval: null,
                    
                    init: function() {
                        const el = this.$el;
                        this.movieId = el.getAttribute('data-movie-id');
                        this.isDownloading = el.getAttribute('data-is-downloading') === 'true';
                        this.completed = parseInt(el.getAttribute('data-completed') || 0);
                        this.total = parseInt(el.getAttribute('data-total') || 0);
                        this.percentage = this.total > 0 ? Math.round((this.completed / this.total) * 100) : 0;

                        if (this.isDownloading) {
                            this.startPolling();
                        }
                    },

                    startDownload: async function() {
                        this.isDownloading = true;
                        
                        try {
                            const response = await fetch('/admin/movies/' + this.movieId + '/episodes/download-all', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            
                            if (response.ok) {
                                this.startPolling();
                            } else {
                                console.error('Failed to start download');
                                this.isDownloading = false;
                                alert('Failed to start download.');
                            }
                        } catch (error) {
                            console.error('Error starting download:', error);
                            this.isDownloading = false;
                        }
                    },

                    startPolling: function() {
                        if (this.interval) clearInterval(this.interval);
                        var self = this;
                        this.interval = setInterval(function() {
                            self.pollProgress();
                        }, 3000);
                        this.pollProgress();
                    },

                    pollProgress: async function() {
                        try {
                            const resp = await fetch('/admin/movies/' + this.movieId + '/episodes/progress');
                            const data = await resp.json();
                            
                            this.total = data.total;
                            this.completed = data.completed;
                            this.percentage = this.total > 0 ? Math.round((this.completed / this.total) * 100) : 0;

                            if (data.is_finished) {
                                clearInterval(this.interval);
                                this.isDownloading = false;
                                if (window.location.pathname.includes('/admin/movies/') && !window.location.pathname.endsWith('/admin/movies') && !window.location.pathname.endsWith('/admin/movies/')) {
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 2000);
                                }
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    }
                };
            });
        });
    </script>
</body>
</html>
