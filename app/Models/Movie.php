<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'resource_id',
        'external_id',
        'title',
        'poster_url',
        'description',
        'metadata',
        'chapter_count',
        'tags',
        'play_count',
        'shelf_time',
        'last_sync_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'shelf_time' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
