<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransFee extends Model
{
    //
    protected $table = 'trans_fee';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
