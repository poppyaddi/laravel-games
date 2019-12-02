<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    //
    protected $table = 'user_log';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function son()
    {
        return $this->belongsTo('App\Models\Son', 'user_id');
    }
}
