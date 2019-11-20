<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //
    protected $guarded = [];

    public function getStatusAttribute($value)
    {
        switch($value){
            case 1:
                return '无效';
                break;
            case 2:
                return '启用';
                break;
            case 3:
                return '禁用';
        }
    }
}
