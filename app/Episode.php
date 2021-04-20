<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $fillable = ['tmdb_id', 'episode_number', 'name', 'overview', 'still_path', 'vote_average', 'vote_count', 'air_date'];



    public function season(){
        return $this->belongsTo(Season::class, 'season_id');
     }
 
     public function videos() {
         return $this->hasMany('App\SerieVideo');
     }

    public function substitles()
    {
        return $this->hasMany('App\SerieSubstitle');
    }


}
