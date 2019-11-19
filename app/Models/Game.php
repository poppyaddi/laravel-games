<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    //
    protected $fillable = ['name', 'productIdentifier', 'description'];

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
