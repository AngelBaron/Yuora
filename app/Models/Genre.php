<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = [
        'name',
        'created_by'
    ];

    public function songs(){
        return $this->belongsToMany(Song::class, 'genre_songs');
    }
}
