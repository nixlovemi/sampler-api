<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Books extends Authenticatable
{
    public $timestamps = false;
    protected $fillable = ['title', 'isbn', 'published_at'];

    public function logs()
     {
         return $this->hasMany('App\Models\UserActionLogs');
     }
}
