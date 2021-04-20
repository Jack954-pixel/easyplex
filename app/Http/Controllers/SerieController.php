<?php

namespace App\Http\Controllers;

use App\Embed;
use App\Episode;
use App\Genre;
use App\Http\Requests\SerieStoreRequest;
use App\Http\Requests\SerieUpdateRequest;
use App\Http\Requests\StoreImageRequest;
use App\Jobs\SendNotification;
use App\Season;
use App\Serie;
use App\SerieGenre;
use App\SerieVideo;
use App\SerieSubstitle;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;



class SerieController extends Controller
{
    // returns all Series except children Series, for api.
    public function index()
    {
        $serie = Serie::whereDoesntHave('genres', function ($genre) {
            $genre->where('genre_id', '=', 10762);
        })->orderByDesc('id')->paginate(12);

        return response()->json($serie, 200);

    }



    // returns all Series for admin panel
    public function data()
    {

        return response()->json(Serie::with('seasons.episodes.videos')->orderByDesc('created_at')->get(), 200);


    
    }

    // returns a specific Serie
    public function show($serie)
    {

       $serie = Serie::where('tmdb_id', '=', $serie)->orWhere('id', '=', $serie)->first();

       $serie->increment('views',1);

        return response()->json($serie, 200);
    }


    // create a new Serie in the database
    public function store(SerieStoreRequest $request)
    {
        $serie = new Serie();
        $serie->fill($request->serie);
        $serie->save();


        $this->onSaveSerieGenre($request,$serie);
        $this->onSaveSerieSeasons($request,$serie);


        if ($request->notification) {
            $this->dispatch(new SendNotification($serie));
        }

        $data = [
            'status' => 200,
            'message' => 'successfully created',
            'body' => $serie->load('seasons.episodes.videos')
        ];

        return response()->json($data, $data['status']);
    }




    public function onSaveSerieGenre($request,$serie) {

        if ($request->serie['genres']) {
            foreach ($request->serie['genres'] as $genre) {
                $find = Genre::find($genre['id']);
                if ($find == null) {
                    $find = new Genre();
                    $find->fill($genre);
                    $find->save();
                }
                $serieGenre = new SerieGenre();
                $serieGenre->genre_id = $genre['id'];
                $serieGenre->serie_id = $serie->id;
                $serieGenre->save();
            }
        }

    }


    public function onSaveSerieSeasons($request , $serie){

        if ($request->serie['seasons']) {
            foreach ($request->serie['seasons'] as $reqSeason) {
                $season = new Season();
                $season->fill($reqSeason);
                $season->serie_id = $serie->id;
                $season->save();
               
                $this->onSaveEpisodes($request,$reqSeason,$season);

                
            }
        }

    }


    public function onSaveEpisodes($request, $reqSeason,$season) {

        if ($reqSeason['episodes']) {
            foreach ($reqSeason['episodes'] as $reqEpisode) {
                $episode = new Episode();
                $episode->fill($reqEpisode);
                $episode->season_id = $season->id;
                $episode->save();


                if (isset($reqEpisode['videos'])) {
                    foreach ($reqEpisode['videos'] as $reqVideo) {
                    
                        $video = SerieVideo::find($reqVideo['id'] ?? 0) ?? new SerieVideo();
                        $video->fill($reqVideo);
                        $video->episode_id = $episode->id;
                        $video->save();
                    }
                }
               

               $this->onSaveEpisodeSubstitle($request,$reqEpisode,$episode);
            }
        }


    }




    public function onSaveEpisodeSubstitle($request,$reqEpisode,$episode) {


         if (isset($reqEpisode['substitles'])) {
                    foreach ($reqEpisode['substitles'] as $reqVideo) {
                        $video = new SerieSubstitle();
                        $video->fill($reqVideo);
                        $video->episode_id = $episode->id;
                        $video->save();
                    }
                }
    }

    // update a Serie in the database
    public function update(SerieUpdateRequest $request, Serie $serie)
    {

        $serie->fill($request->serie);
        $serie->save();

        $this->onUpdateSerieGenre($request,$serie);
        $this->onUpdateSerieSeasons($request,$serie);


        $data = [
            'status' => 200,
            'message' => 'successfully updated',
            'body' => Serie::all()
        ];

        return response()->json($data, $data['status']);
    }




    public function onUpdateSerieGenre ($request,$serie) {

        if ($request->serie['genres']) {
            foreach ($request->serie['genres'] as $genre) {
                if (!isset($genre['genre_id'])) {
                    $find = Genre::find($genre['id']) ?? new Genre();
                    $find->fill($genre);
                    $find->save();
                    $serieGenre = SerieGenre::where('serie_id', $serie->id)->where('genre_id', $genre['id'])->get();
                    if (count($serieGenre) < 1) {
                        $serieGenre = new SerieGenre();
                        $serieGenre->genre_id = $genre['id'];
                        $serieGenre->serie_id = $serie->id;
                        $serieGenre->save();
                    }
                }
            }
        }

    }


    public function onUpdateSerieSeasons($request,$serie){


        if ($request->serie['seasons']) {
            foreach ($request->serie['seasons'] as $reqSeason) {
                $season = Season::find($reqSeason['id'] ?? 0) ?? new Season();
                $season->fill($reqSeason);
                $season->serie_id = $serie->id;
                $season->save();


                $this->onUpdateSerieEpisodes($request,$reqSeason,$season);
            }
        }


    }




    public function onUpdateSerieEpisodes ($request,$reqSeason,$season) {

        if ($reqSeason['episodes']) {
                    foreach ($reqSeason['episodes'] as $reqEpisode) {
                        $episode = Episode::find($reqEpisode['id'] ?? 0) ?? new Episode();
                        $episode->fill($reqEpisode);
                        $episode->season_id = $season->id;
                        $episode->save();
                        if (isset($reqEpisode['videos'])) {
                            foreach ($reqEpisode['videos'] as $reqVideo) {
                            
                                $video = SerieVideo::find($reqVideo['id'] ?? 0) ?? new SerieVideo();
                                $video->fill($reqVideo);
                                $video->episode_id = $episode->id;
                                $video->save();
                            }
                        }

                
                        $this->onUpdateSerieSubstitle($request,$reqEpisode,$episode);
                    }
                }

    }



    public function onUpdateSerieSubstitle ($request,$reqEpisode,$episode) {

        if (isset($reqEpisode['substitles'])) {
            foreach ($reqEpisode['substitles'] as $reqVideo) {

                $substitle = SerieSubstitle::find($reqVideo['id'] ?? 0) ?? new SerieSubstitle();
                $substitle->fill($reqVideo);
                $substitle->episode_id = $episode->id;
                $substitle->save();
            }
        

    }

}


    // delete a Serie from the database
    public function destroy(Serie $serie)
    {
        if ($serie != null) {
            $serie->delete();

            $data = [
                'status' => 200,
                'message' => 'successfully deleted',
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'could not be deleted',
            ];
        }


        return response()->json($data, $data['status']);
    }

    // remove a genre from a Series from the database
    public function destroyGenre(SerieGenre $genre)
    {
        if ($genre != null) {
            $genre->delete();
            $data = [
                'status' => 200,
                'message' => 'successfully deleted',
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'could not be deleted',
            ];
        }

        return response()->json($data, $data['status']);
    }

    // save a new image in the Series folder of the storage
    public function storeImg(StoreImageRequest $request)
    {

        if ($request->hasFile('image')) {
            $filename = Storage::disk('series')->put('', $request->image);
            $data = [
                'status' => 200,
                'image_path' => $request->root() . '/api/series/image/' . $filename,
                'message' => 'image uploaded successfully'
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'there was an error uploading the image'
            ];
        }

        return response()->json($data, $data['status']);
    }

    // return an image from the Series folder of the storage
    public function getImg($filename)
    {

        $image = Storage::disk('series')->get($filename);

        $mime = Storage::disk('series')->mimeType($filename);

        return (new Response($image, 200))
            ->header('Content-Type', $mime);
    }


    // returns a specific Serie
    public function showbyimdb($serie)
    {

        $serie_by_imdbid = Serie::where('tmdb_id', '=', $serie)->first();


        $serie_by_imdbid->increment('views',1);


        return response()->json($serie_by_imdbid, 200);
    }




    // return the 10 Series with the highest average votes
    public function recommended()
    {
        $serie = Serie::orderByDesc('vote_average')->where('active', '=', 1)->where('active', '=', 1)->limit(10)->get();

        return response()->json(['recommended' => $serie->makeHidden('seasons','episodes')], 200);
    }

    // return the 10 movies with the most popularity
    public function popular()
    {


        $serie = Serie::where('featured', 1)->where('active', '=', 1)
            ->orderByDesc('popularity')->limit(10)
            ->get();
        
        return response()->json(['popularSeries' => $serie->makeHidden('seasons','episodes')], 200);
      

    }

    // returns the last 10 Series added in the month
    public function recents()
    {
        $serie = Serie::where('created_at', '>', Carbon::now()->subMonth())
        ->where('active', '=', 1)
        ->orderByDesc('created_at')
        ->limit(15)->get();

        return response()->json(['recents' => $serie->makeHidden('seasons','episodes')], 200);

    }



}
