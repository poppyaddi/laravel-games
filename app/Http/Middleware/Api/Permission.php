<?php

namespace App\Http\Middleware\Api;

use App\Models\Perm;
use App\Models\Role;
use App\Models\RolePerm;
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
