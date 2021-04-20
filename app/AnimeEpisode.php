<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeEpisode extends Model
{
    protected $fillable = ['tmdb_id', 'episode_number', 'name', 'overview', 'still_path', 'vote_average', 'vote_count', 'air_date'];


    public function season()
    {

        return $this->belongsTo(AnimeSeason::class, 'season_id');
    }

    public function videos()
    {
        return $this->hasMany('App\AnimeVideo');
    }
}
