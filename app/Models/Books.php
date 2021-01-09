<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    public $timestamps = false;

    public function logs()
     {
         return $this->hasMany('App\Models\UserActionLogs');
     }
}
