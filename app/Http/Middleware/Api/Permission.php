<?php

namespace App\Http\Middleware\Api;

use App\Models\Perm;
use App\Models\Role;
use App\Models\RolePerm;
use App\Models\User;
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
        # $request->getClientIp() 获取反向代理ip
        # $brower = $_SERVER['HTTP_USER_AGENT'] 获取浏览器信息
        # 获取token $token = $request->header()['authorization'][0]
        # 浏览器信息与token md5存入数据库
        # 判断是否为相同浏览器登陆
        $user = auth('api')->user();

        $brower = $_SERVER['HTTP_USER_AGENT'];
        $m_token = $request->header()['authorization'][0];
        $remember_token = sha1($brower . $m_token);
        if($user->remember_token != $remember_token){
            return error('', 403, '身份验证失败');
        }

        # 用户禁用或者月租用户过期，禁止登录 如果$flag为false禁止登录

        $flag = $user->status == 1 ? true : false;
        $user_info = UserInfo::where('user_id', $user->id)->first();
        if($user_info){
            # 管理员没有userinfo
            if($user_info->charge_status == '月租收费'){
                # 判断过期时间
                if(strtotime($user_info->expire_time) < time()){
                    $flag = false;
                }
            }
            # $flag为false则禁止再次访问，返回登录界面
            if(!$flag){
                return error('', 403, '账户失效');
            }
        }

        $path = $request->path();
        # api略过2，web略过1
        $start = substr($path, 0, 3);
        $offset = $start == 'api' ? 2 : 1;
        $path = array_slice(explode('/', $path),$offset);
        $path =implode('/',$path);
        $role_id = Role::get_role_id();

        $perm_id = RolePerm::where('role_id', $role_id)->first()->perm_id;
        $perm_id = $perm_id != '' ? explode(',', $perm_id): [];
        $perms = Perm::whereIn('id', $perm_id)->pluck('uri')->toArray();
//        return success($perms);
        return in_array($path, $perms) ? $next($request) : error('', 401, '无权限');
    }
}
