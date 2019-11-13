<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePerm extends Model
{
    //
    protected $table = 'role_perm';
    public $timestamps = false;
    protected $fillable = ['role_id', 'perm_id'];
}
