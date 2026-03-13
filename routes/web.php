<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocsController;
use App\Http\Controllers\Admin\EpisodeController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Docs
    Route::get('/docs/api', [DocsController::class, 'api'])->name('docs.api');

    Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
    Route::post('/movies/sync', [MovieController::class, 'sync'])->name('movies.sync');
    Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
    Route::get('/movies/{movie}/play/{episode?}', [MovieController::class, 'play'])->name('movies.play');
    Route::get('/movies/{movie}/export', [MovieController::class, 'exportJson'])->name('movies.export');

    Route::post('/movies/{movie}/episodes/sync', [EpisodeController::class, 'sync'])->name('episodes.sync');
    Route::post('/movies/{movie}/episodes/download-all', [EpisodeController::class, 'downloadAll'])->name('episodes.downloadAll');
    Route::get('/movies/{movie}/episodes/progress', [EpisodeController::class, 'progress'])->name('episodes.progress');
    Route::post('/episodes/{episode}/download', [EpisodeController::class, 'download'])->name('episodes.download');
    Route::get('/episodes/{episode}/export', [EpisodeController::class, 'exportJson'])->name('episodes.export');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
