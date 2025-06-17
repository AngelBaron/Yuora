<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable=[
        'user_id',
        'content'
    ];

    public function post_media(){
        return $this->hasMany(Post_Media::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
