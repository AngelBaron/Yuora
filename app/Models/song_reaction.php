<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class song_reaction extends Model
{
    protected $fillable =
    [
        'user_id',
        'song_id',
        'type'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function song(){
        return $this->belongsTo(Song::class);
    }
}