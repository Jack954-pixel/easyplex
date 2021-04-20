<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{


    protected $guarded = [];

    protected $casts = [
        'tmdb_lang' => 'array',
        'autosubstitles' => 'int',
        'livetv' => 'int',
        'kids' => 'int',
        'livetv' => 'int',
        'ads_player' => 'int',
        'anime' => 'int',
        'ad_banner' => 'int',
        'ad_face_audience_interstitial' => 'int',
        'ad_face_audience_banner' => 'int',
        'featured_home_numbers' => 'int',
        'enable_custom_message' => 'int',
        'wach_ads_to_unlock' => 'int',
        'ad_interstitial' => 'int',
        'next_episode_timer' => 'int',
        'appodeal_show_interstitial' => 'int',
        'ad_unit_id_native_enable' => 'int',
        'appodeal_banner' => 'int',
        'appodeal_interstitial' => 'int',
        'server_dialog_selection' => 'int',
        'download_premuim_only' => 'int'
        
    ];
}
