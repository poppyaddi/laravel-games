<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InoutLog extends Model
{
    //
    protected $table = 'inout_logs';
    protected $guarded = [];

    public function son()
    {
        return $this->belongsTo('App\Models\Son', 'user_id');
    }

    public function store()
    {
        return $this->belongsTo('App\Models\Store', 'store_id');
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
                return '标记出库成功';
        }
    }
}
