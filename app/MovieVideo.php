<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieVideo extends Model
{
    protected $fillable = ['server', 'link', 'lang', 'embed', 'status'];

    public function movie()
    {
        return $this->belongsTo('App\Movie');
    }

}
