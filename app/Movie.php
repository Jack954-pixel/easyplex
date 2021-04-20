<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = ['tmdb_id','imdb_external_id', 'title', 'overview', 'poster_path', 'backdrop_path', 'preview_path',
     'vote_average', 'vote_count', 'popularity', 'runtime', 'views','featured', 'premuim','active', 'release_date'];

    protected $appends = ['hd', 'genreslist', 'substitle','substype'];

    protected $casts = [
        'status' => 'int',
        'premuim' => 'int',
        'featured' => 'int'
    ];


    public function genres()
    {
        return $this->hasMany('App\MovieGenre');
    }

    public function videos()
    {
        return $this->hasMany('App\MovieVideo');
    }


    public function substitles()
    {
        return $this->hasMany('App\MovieSubstitle');
    }


    public function getSubstitleAttribute()
    {

        $subs = 0;
        $substitles = $this->substitles;
        if ($substitles) {
            foreach ($substitles as $substitle) {
                if ($substitle->lang) {
                    $subs = 1;
                }
            }
        }

        return $subs;

    }

    public function getHdAttribute()
    {
        $hd = 0;
        $videos = $this->videos;
        if ($videos) {
            foreach ($videos as $video) {
                if ($video->hd) {
                    $hd = 1;
                }
            }
        }

        return $hd;
    }


    public function getSubsTypeAttribute()
    {
        $substype = 0;
        $substitles = $this->substitles;
        if ($substitles) {
            foreach ($substitles as $substitle) {
                if ($substitle->type) {
                    $substype = $substitle->type;
                }
            }
        }

        return $substype;
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
