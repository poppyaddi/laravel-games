<?php

namespace App\Http\Middleware\Port;

use App\Http\Controllers\Port\BaseController;
use App\Http\Controllers\Port\Rsa1024Controller;
use Closure;

class Permission extends BaseController
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
        $parent_info = $this->parent();
        # 如果是月租收费, 判断过期时间
        if($parent_info->userinfo->charge_status == '月租收费'){
            $expire_time = $parent_info->userinfo->expire_time;
            if(strtotime($expire_time) < time()){
                $rsa = new Rsa1024Controller();
                return $rsa->RSA_private_encrypt(err('账户过期'));
            }
        }
        return $next($request);
    }
}
