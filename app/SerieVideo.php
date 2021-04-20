<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SerieVideo extends Model
{
    protected $fillable = ['episode_id','server','link','embed', 'lang'];

    public function episode()
    {
        return $this->belongsTo('App\Episode');
    }
}
