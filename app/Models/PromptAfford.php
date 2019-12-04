<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptAfford extends Model
{
    //
    protected $table = 'prompt_afford';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\UserInfo', 'user_id', 'user_id');
    }
}
