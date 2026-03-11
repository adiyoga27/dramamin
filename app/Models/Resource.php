<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'name',
        'api_url',
        'api_key',
        'status'
    ];

    public function movies()
    {
        return $this->hasMany(Movie::class);
    }
}
