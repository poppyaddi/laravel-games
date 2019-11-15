<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    //
    public $timestamps = false;
    protected $fillable = ['key', 'value', 'description'];

    public static function get_value($key)
    {
        return self::where('key', $key)->first()->value;
    }
}
