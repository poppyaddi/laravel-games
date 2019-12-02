<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Money extends Model
{
    //
    protected $table = 'money';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function getStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return '未审核';
                break;
            case 2:
                return '通过审核';
                break;
            case 3:
                return '拒绝审核';
        }
    }

    public function getTypeAttribute($value)
    {
        switch ($value){
            case 1:
                return '充值';
                break;
            case 2:
                return '提现';
        }
    }

    public function getAccountTypeAttribute($value)
    {
        switch ($value){
            case 1:
                return '支付宝';
                break;
            case 2:
                return '微信';
                break;
            case 3:
                return '其他';
        }
    }
}
