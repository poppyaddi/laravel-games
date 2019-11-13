<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Role;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function username()
    {
        return 'name';
    }


    //
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'password', 'remember_token'];


    public function getStatusAttribute($value)
    {
        switch ($value){
            case 1:
                return true;
            case 2:
                return false;
        }
    }

    /**
     * 反向关联角色模型(多个用户都属于同一个角色)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id');
    }

    public static function status($status)
    {
        switch ($status){
            case false:
                return 1;
            case true:
                return 2;
        }
    }

    public function setPasswordAttribute($value){
        $this->attributes['password'] = bcrypt($value);
    }

    public static function get_status($status)
    {
        if(!$status){
            $status = ['1', '2'];
        }
        return $status;
    }
}
