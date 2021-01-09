<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    public $timestamps = false; // prevent created/updated_at

    public function logs()
     {
         return $this->hasMany('App\Models\UserActionLogs');
     }
}
