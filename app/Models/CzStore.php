<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CzStore extends Model
{
    //
    protected $connection = 'temp';
    protected $table = 'cz_store';
    protected $hidden = ['start_time', 'end_time', 'use_time', 'create_time', 'update_time'];
}
