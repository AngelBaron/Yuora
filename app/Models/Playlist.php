<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable= [
        'name',
        'photo',
        'user_id'
    ];


    public function songs(){
        $this->belongsToMany(Song::class,'song_playlist');
    }
}
