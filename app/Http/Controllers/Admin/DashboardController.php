<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Episode;
use App\Models\Resource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_resources' => Resource::count(),
            'total_movies' => Movie::count(),
            'total_episodes' => Episode::count(),
            'recent_movies' => Movie::with('resource')->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
