<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CzPrice extends Model
{
    //
    protected $connection = 'temp';
    protected $table = 'cz_games_price';
    protected $hidden = ['create_time', 'update_time'];
    public function store(){
        return $this->hasMany('App\Models\CzStore', 'price_id', 'id');
    }
}
