<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActionLogs extends Model
{
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\Users');
    }

    public function book()
    {
        return $this->belongsTo('App\Models\Books');
    }
}
