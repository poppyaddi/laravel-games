<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{

    protected $guarded = [];

    public function price()
    {
        return $this->belongsTo('App\Models\Price');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'owner_user_id', 'id');
    }



    public function son()
    {
        return $this->belongsTo('App\Models\Son', 'owner_user_id', 'id');
    }

    public function input()
    {
        return $this->belongsTo('App\Models\Son', 'input_user_id', 'id');
    }

    public function getStatusAttribute($value)
    {
        switch($value){
            case 1:
                return '正常有效';
                break;
            case 2:
                return '已使用';
                break;
            case 3:
                return '已过期';
                break;
            case 4:
                return '使用失败';
                break;
            case 5:
                return '后台恢复';
                break;
            case 6:
                return '手机端已获取';
                break;
            case 7:
                return '禁用';
                break;
            case 8:
                return '上架';
                break;

        }
    }
}
