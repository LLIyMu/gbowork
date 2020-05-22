<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class category extends Model
{
    //
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function sluggable()
    {
        return  Str::slug('title', '-');
    }
}
