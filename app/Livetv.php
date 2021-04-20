<?php

namespace App;




use Illuminate\Database\Eloquent\Model;

class Livetv extends Model


{
    protected $fillable = ['name', 'overview', 'poster_path', 'backdrop_path', 'link','vip','featured','embed'];



    protected $appends = ['genreslist'];



    public function genres()
    {
        return $this->hasMany('App\LivetvGenre');
    }




    public function getGenreslistAttribute()
    {
        $genres = [];
        foreach ($this->genres as $genre) {
            array_push($genres, $genre['name']);
        }
        return $genres;
    }


}
