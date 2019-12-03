<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Afford extends Model
{
    //
    protected $table = 'afford';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\UserInfo', 'user_id', 'user_id');
    }

    public function price()
    {
        return $this->belongsTo('App\Models\Price', 'price_id');
    }

    public function getStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return '未供货';
                break;
            case 2:
                return '部分供货';
                break;
            case 3:
                return '完全供货';
                break;
        }
    }
}
