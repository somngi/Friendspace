<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    //
    protected $table = 'user_photo_album';

    public function photos(){
        return $this->hasMany(Photo::class,'album_id');
    }
}
