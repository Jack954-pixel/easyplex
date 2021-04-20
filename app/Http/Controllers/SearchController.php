<?php

namespace App\Http\Controllers;

use App\Movie;
use App\Serie;
use App\Livetv;
use App\Anime;

class SearchController extends Controller
{
    // returns all the movies, animes and livetv that match the search
    public function index($query)
    {


        $movies = Movie::select('*')->where('title', 'LIKE', "%$query%")->where('active', '=', 1)->limit(10)->get();
        $series = Serie::select('*')->where('name', 'LIKE', "%$query%")->where('active', '=', 1)->limit(10)->get();
        $stream = Livetv::select('*')->where('name', 'LIKE', "%$query%")->limit(10)->get();
        $anime = Anime::select('*')->where('name', 'LIKE', "%$query%")->where('active', '=', 1)->limit(10)->get();
        $array = array_merge($movies->toArray(), $series->makeHidden('seasons','episodes')->toArray(),
        
        $stream->makeHidden('seasons','episodes')->toArray(), $anime->makeHidden('seasons','episodes')->toArray());


        return response()->json(['search' => $array], 200);
    }
}