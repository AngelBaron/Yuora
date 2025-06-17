<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post_media extends Model
{
    protected $table = 'post_media';
    protected $fillable=[
        'post_id',
        'type',
        'path',
        'title',
        'description'
    ];
    public function post(){
        return $this->belongsTo(Post::class);
    }
}
