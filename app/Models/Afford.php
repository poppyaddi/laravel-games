<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Afford extends Model
{
    //
    protected $table = 'afford';
    protected $guarded = [];

    protected $appends = ['expire_time'];

    public function getExpireTimeAttribute()
    {
        return (strtotime($this->attributes['created_at']) + Config::get_value('affore_store_expire_time') * 24*60*60) * 1000;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\UserInfo', 'user_id', 'user_id');
    }

    public function price()
    {
        return $this->belongsTo('App\Models\Price', 'price_id');
    }

    public function game()
    {
        return $this->belongsTo('App\Models\Game', 'game_id');
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
            case 4:
                return '惩罚';
                break;
        }
    }
}
