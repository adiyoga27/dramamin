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
        'last_sync_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_sync_at' => 'datetime'
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
