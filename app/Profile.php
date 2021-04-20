<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['name','image','status'];


    protected $appends = ['hd'];




    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function getHdAttribute()
    {
        $hd = 0;
        $users = $this->profiles;
        if ($users) {
            foreach ($users as $user) {
                if ($user->hd) {
                    $hd = 1;
                }
            }
        }

        return $hd;
    }

}
