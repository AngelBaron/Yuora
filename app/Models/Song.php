<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'artist_id',
        'title',
        'photo_song',
        'audio_song',
        'album_id'
    ];

    public function genres(){
        return $this->belongsToMany(Genre::class,'genre_songs');
    }

    public function artist(){
        return $this->belongsTo(Artist::class);
    }

    public function album(){
        return $this->belongsTo(Album::class);
    }
}
