<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    //
    protected $fillable =[
        'name_album',
        'photo_album'
    ];

    public function songs(){
        return $this->hasMany(Song::class);
    }
}
