<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawFee extends Model
{
    //
    protected $table = 'withdraw_fee';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
