<?php

namespace App\Http\Controllers;

use App\Embed;
use App\AnimeEpisode;
use App\Genre;
use App\Http\Requests\AnimeStoreRequest;
use App\Http\Requests\AnimeUpdateRequest;
use App\Http\Requests\StoreImageRequest;
use App\Jobs\SendNotification;
use App\AnimeSeason;
use App\Anime;
use App\AnimeGenre;
use App\AnimeVideo;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;


class AnimeController extends Controller
{




// returns all animes except children animes, for api.
public function index()
{
    $anime = Anime::whereDoesntHave('genres', function ($genre) {
        $genre->where('genre_id', '=', 10762);
    })->orderByDesc('id')->paginate(12);

    return response()->json($anime, 200);

}



// returns all animes for admin panel
public function data()
{
    return response()->json(Anime::with('seasons.episodes.videos')->orderByDesc('created_at')->get(), 200);
}

// returns a specific anime
public function show($anime)
{
    $anime = Anime::where('tmdb_id', '=', $anime)->orWhere('id', '=', $anime)->first();

    $anime->increment('views',1);

     return response()->json($anime, 200);
}


// create a new anime in the database
public function store(AnimeStoreRequest $request)
{
    $anime = new Anime();
    $anime->fill($request->anime);
    $anime->save();


    $this->onSaveAnimeGenre($request,$anime);
    $this->onSaveAnimeSeasons($request,$anime);



    if ($request->notification) {
        $this->dispatch(new SendNotification($anime));
    }

    $data = [
        'status' => 200,
        'message' => 'successfully created',
        'body' => $anime->load('seasons.episodes.videos')
    ];

    return response()->json($data, $data['status']);
}




public function onSaveAnimeGenre($request,$anime) {

    if ($request->anime['genres']) {
        foreach ($request->anime['genres'] as $genre) {
            $find = Genre::find($genre['id']);
            if ($find == null) {
                $find = new Genre();
                $find->fill($genre);
                $find->save();
            }
            $animeGenre = new AnimeGenre();
            $animeGenre->genre_id = $genre['id'];
            $animeGenre->anime_id = $anime->id;
            $animeGenre->save();
        }
    }

}


public function onSaveAnimeSeasons($request , $anime){

    if ($request->anime['seasons']) {
        foreach ($request->anime['seasons'] as $reqSeason) {
            $season = new AnimeSeason();
            $season->fill($reqSeason);
            $season->anime_id = $anime->id;
            $season->save();
           
            $this->onSaveEpisodes($request,$reqSeason,$season);

            
        }
    }

}


public function onSaveEpisodes($request, $reqSeason,$season) {

    if ($reqSeason['episodes']) {
        foreach ($reqSeason['episodes'] as $reqEpisode) {
            $episode = new AnimeEpisode();
            $episode->fill($reqEpisode);
            $episode->anime_season_id = $season->id;
            $episode->save();


            if (isset($reqEpisode['videos'])) {
                foreach ($reqEpisode['videos'] as $reqVideo) {
                    if (!filter_var($reqVideo['link'], FILTER_VALIDATE_URL)) {
                        $embed = new Embed();
                        $embed->code = $reqVideo['link'];
                        $embed->save();
                        $reqVideo['link'] = $request->root() . '/api/embeds/show/' . $embed->id;
                    }
                    $video = AnimeVideo::find($reqVideo['id'] ?? 0) ?? new AnimeVideo();
                    $video->fill($reqVideo);
                    $video->anime_episode_id = $episode->id;
                    $video->save();
                }
            }
        
        }
    }


}


// update a anime in the database
public function update(AnimeUpdateRequest $request, Anime $anime)
{

    $anime->fill($request->anime);
    $anime->save();

    $this->onUpdateAnimeGenre($request,$anime);
    $this->onUpdateAnimeSeasons($request,$anime);


    $data = [
        'status' => 200,
        'message' => 'successfully updated',
        'body' => Anime::all()
    ];

    return response()->json($data, $data['status']);
}




public function onUpdateAnimeGenre ($request,$anime) {

    if ($request->anime['genres']) {
        foreach ($request->anime['genres'] as $genre) {
            if (!isset($genre['genre_id'])) {
                $find = Genre::find($genre['id']) ?? new Genre();
                $find->fill($genre);
                $find->save();
                $animeGenre = AnimeGenre::where('anime_id', $anime->id)->where('genre_id', $genre['id'])->get();
                if (count($animeGenre) < 1) {
                    $animeGenre = new AnimeGenre();
                    $animeGenre->genre_id = $genre['id'];
                    $animeGenre->anime_id = $anime->id;
                    $animeGenre->save();
                }
            }
        }
    }

}


public function onUpdateAnimeSeasons($request,$anime){


    if ($request->anime['seasons']) {
        foreach ($request->anime['seasons'] as $reqSeason) {
            $season = AnimeSeason::find($reqSeason['id'] ?? 0) ?? new AnimeSeason();
            $season->fill($reqSeason);
            $season->anime_id = $anime->id;
            $season->save();


            $this->onUpdateAnimeEpisodes($request,$reqSeason,$season);
        }
    }
}




public function onUpdateAnimeEpisodes ($request,$reqSeason,$season) {

    if ($reqSeason['episodes']) {
                foreach ($reqSeason['episodes'] as $reqEpisode) {
                    $episode = AnimeEpisode::find($reqEpisode['id'] ?? 0) ?? new AnimeEpisode();
                    $episode->fill($reqEpisode);
                    $episode->anime_season_id = $season->id;
                    $episode->save();
                    if (isset($reqEpisode['videos'])) {
                        foreach ($reqEpisode['videos'] as $reqVideo) {
                            if (!filter_var($reqVideo['link'], FILTER_VALIDATE_URL)) {
                                $embed = new Embed();
                                $embed->code = $reqVideo['link'];
                                $embed->save();
                                $reqVideo['link'] = $request->root() . '/api/embeds/show/' . $embed->id;
                            }
                            $video = AnimeVideo::find($reqVideo['id'] ?? 0) ?? new AnimeVideo();
                            $video->fill($reqVideo);
                            $video->anime_episode_id = $episode->id;
                            $video->save();
                        }
                    }
                }
            }

}



// delete a anime from the database

    public function destroy(Anime $anime)
    {
        if ($anime != null) {
            $anime->delete();

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


// remove a genre from a animes from the database
public function destroyGenre(AnimeGenre $genre)
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

// save a new image in the animes folder of the storage
public function storeImg(StoreImageRequest $request)
{

    if ($request->hasFile('image')) {
        $filename = Storage::disk('animes')->put('', $request->image);
        $data = [
            'status' => 200,
            'image_path' => $request->root() . '/api/animes/image/' . $filename,
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

// return an image from the animes folder of the storage
public function getImg($filename)
{

    $image = Storage::disk('animes')->get($filename);

    $mime = Storage::disk('animes')->mimeType($filename);

    return (new Response($image, 200))
        ->header('Content-Type', $mime);
}


// returns a specific anime
public function showbyimdb($anime)
{

    $anime_by_imdbid = Anime::where('tmdb_id', '=', $anime)->first();


    return response()->json($anime_by_imdbid, 200);
}


// returns the last 10 animes added in the month
public function recents()
{
    $anime = Anime::orderByDesc('created_at')->limit(15)
    ->get();


    return response()->json(['anime' => $anime->makeHidden('seasons','episodes')], 200);

}


}
