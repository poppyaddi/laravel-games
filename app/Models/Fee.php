<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    //
    protected $table = 'fee';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
