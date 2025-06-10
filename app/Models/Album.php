<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    //
    protected $fillable =[
        'artist_id',
        'name_album',
        'photo_album'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function songs(){
        return $this->hasMany(Song::class);
    }
}
