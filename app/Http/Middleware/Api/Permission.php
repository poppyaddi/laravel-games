<?php

namespace App\Http\Middleware\Api;

use App\Models\Perm;
use App\Models\Role;
use App\Models\RolePerm;
use App\Models\UserInfo;
use Closure;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $flag = true;
        $user = auth('api')->user();
        $status = $user->status;
        $user_info = UserInfo::where('user_id', $user->id)->find();
        $expire_time = $user_info->expire_time;
        $charge_status = $user_info->charge_status;
        // 如果是禁用 $flag=false; 如果charge_status是出库收费，并且时间过期也禁用
        $path = $request->path();
        $path = array_slice(explode('/', $path),2);
        $path =implode('/',$path);
        $role_id = Role::get_role_id();

        $perm_id = RolePerm::where('role_id', $role_id)->first()->perm_id;
        $perm_id = $perm_id != '' ? explode(',', $perm_id): [];
        $perms = Perm::whereIn('id', $perm_id)->pluck('uri')->toArray();
//        return success($perms);
        return in_array($path, $perms) ? $next($request) : error('', 401, '无权限');
    }
}
