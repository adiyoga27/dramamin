<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $fillable = [
        'movie_id',
        'external_id',
        'title',
        'download_url',
        'local_path',
        'status'
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
