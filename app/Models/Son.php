<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Model;

class Son extends Model
{
    //
    protected $fillable = ['name', 'password', 'type', 'user_id'];
    protected $hidden = ['password', 'updated_at'];

    public function setPasswordAttribute($value){
        $this->attributes['password'] = bcrypt($value);
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }


    public static function get_account_nums()
    {
        return self::where('user_id', auth()->user()->id)->count();
    }

    public function getTypeAttribute($value)
    {
        switch ($value){
            case 1:
                return '入库';
                break;
            case 2:
                return '出库';
                break;
            case 3:
                return '入库出库';
                break;
        }
    }

    public function getStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return '启用';
                break;
            case 2:
                return '禁用';
        }
    }

    public static function handleType($value)
    {
        switch ($value){
            case '入库':
                return 1;
                break;
            case '出库':
                return 2;
                break;
            case '入库出库':
                return 3;
                break;
            default:
                return $value;
        }
    }

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


}
