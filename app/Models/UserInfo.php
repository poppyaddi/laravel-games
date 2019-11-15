<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    //
    public $timestamps = false;
    protected $table = 'userinfo';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getChargeStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return '月租收费';
                break;
            case 2:
                return '出库收费';
                break;
        }
    }

    public function getSaveDeviceAttribute($value)
    {
        switch ($value){
            case 1:
                return '需启用';
                break;
            case 2:
                return '无需启用';
                break;
        }
    }

    public function getStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return true;
            case 2:
                return false;
        }
    }

    public static function handleChargeStatus($data)
    {
        switch($data['charge_status']){
            case '月租收费':
                $data['charge_status'] = 1;
                break;
            case '出库收费':
                $data['charge_status'] = 2;
                break;
        }
        return $data;
    }

    public static function handleAdmin($data)
    {
        switch($data['admin']){
            case '可添加':
                $data['admin'] = 1;
                break;
            case '不可添加':
                $data['admin'] = 2;
                break;
        }
        return $data;
    }

    public static function handleSaveDevice($data)
    {
        switch($data['save_device']){
            case '需启用':
                $data['save_device'] = 1;
                break;
            case '无需启用':
                $data['save_device'] = 2;
                break;
        }
        return $data;
    }

    public static function handlePassStore($data)
    {
        switch($data['pass_store']){
            case '跳过':
                $data['pass_store'] = 1;
                break;
            case '不跳过':
                $data['pass_store'] = 2;
                break;
        }
        return $data;

    }

    public function getExpireTimeAttribute($value)
    {
        return substr($value, 0, 10);
    }
}
