<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
    protected $fillable = ['name', 'description'];
    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->hasMany('App\Models\User');
    }

    public static function get_role_id()
    {
        return User::where('id', auth()->user()->id)->with('role')->first()->role->id;
    }
}
