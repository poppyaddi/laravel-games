<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleLog extends Model
{
    //
    protected $table = 'sale_logs';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\UserInfo', 'user_id', 'user_id');
    }
}
