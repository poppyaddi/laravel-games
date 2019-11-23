<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CzGame extends Model
{
    //
    protected $connection = 'temp';
    protected $table = 'cz_games';
    protected $hidden = ['created_at', 'updated_at'];

    public function price(){
        return $this->hasMany('App\Models\CzPrice', 'gs_id', 'gs_id');
    }
}
