<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    //
    protected $fillable = ['gold', 'title', 'money', 'game_id'];

    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function getStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return '启用';
                break;
            case 2:
                return '禁用';
                break;
        }
    }

//    public function getMoneyAttribute($value)
//    {
//        return $value . '元';
//    }

    public static function handleStatus($value){
        switch ($value){
            case '启用':
                return 2;
                break;
            case '禁用':
                return 1;
                break;
            default:
                return $value;
        }
    }

    public function store()
    {
        return $this->hasMany('App\Models\Store', 'price_id', 'id');
    }
}
