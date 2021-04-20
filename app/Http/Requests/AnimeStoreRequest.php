<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnimeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'anime.tmdb_id' => 'integer|unique:animes,tmdb_id',
            'anime.name' => 'required',
            'anime.vote_average' => 'nullable|numeric',
            'anime.vote_count' => 'nullable|numeric',
            'anime.popularity' => 'nullable|numeric',
            'anime.release_date' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'anime.tmdb_id.integer' => 'the tmdb_id must be an integer.',
            'anime.tmdb_id.unique' => 'the tmdb_id is already exists in the database.',
            'anime.name.required' => 'the name is required.',
            'anime.vote_average.numeric' => 'the vote_average must be a number',
            'anime.vote_count.numeric' => 'the vote_count must be a number',
            'anime.popularity.numeric' => 'the popularity must be a number',
            'anime.release_date.numeric' => 'the release_date must be a date',
        ];
    }
}
