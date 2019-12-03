<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buy extends Model
{
    //
    protected $table = 'buy';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\UserInfo', 'user_id', 'user_id');
    }

    public function game()
    {
        return $this->belongsTo('App\Models\Game', 'game_id');
    }

    public function price()
    {
        return $this->belongsTo('App\Models\Price', 'price_id');
    }

    public function getStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return '正常挂单';
                break;
            case 2:
                return '部分交易';
                break;
            case 3:
                return '交易完成';
                break;
            case 4:
                return '订单下架';
                break;
        }
    }
}
