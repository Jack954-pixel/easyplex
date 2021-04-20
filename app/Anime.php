<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    protected $fillable = ['tmdb_id', 'name', 'overview', 'poster_path', 'backdrop_path', 
    'preview_path', 'vote_average', 'vote_count', 'popularity', 'premuim','views', 'featured', 'first_air_date', 'tv'];

    protected $with = ['genres', 'seasons'];
    protected $appends = ['hd', 'genreslist'];

    protected $casts = [
        'status' => 'int',
        'premuim' => 'int',
        'featured' => 'int'
    ];


    public function genres()
    {
        return $this->hasMany('App\AnimeGenre');
    }

    public function seasons()
    {
        return $this->hasMany('App\AnimeSeason')->orderBy('season_number');
    }

    public function getHdAttribute()
    {
        $hd = 0;

        foreach ($this->seasons as $season) {
            foreach ($season->episodes as $episode) {
                foreach ($episode->videos as $video) {
                    if ($video->hd) {
                        $hd = 1;
                    }
                }
            }
        }

        return $hd;
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
