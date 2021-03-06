<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        # 这里额外注意了：官方文档样例中只除外了『login』
        # 这样的结果是，token 只能在有效期以内进行刷新，过期无法刷新
        # 如果把 refresh 也放进去，token 即使过期但仍在刷新期以内也可刷新
        # 不过刷新一次作废
        $this->middleware('auth:api', ['except' => ['login', 'refresh']]);
        parent::__construct();
        # 另外关于上面的中间件，官方文档写的是『auth:api』
        # 但是我推荐用 『jwt.auth』，效果是一样的，但是有更加丰富的报错信息返回
    }

    public function login()
    {
        # 1. 首先判断验证码是否正确正确

        # 2. 验证账户
        $credentials = request(['name', 'password']);
        $credentials['status'] = 1;

        # 3. 登录失败插入日志
        $data = [
            'login_ip' => request()->getClientIp(),
            'type' => 1
        ];



        if (! $token = auth('api')->attempt($credentials)) {
            # 插入日志
            $data['description'] = '登陆失败, 用户名"' . json_encode($credentials);
            UserLog::create($data);

            return response()->json(['message' => '登录失败, 请检查用户名和密码或账户状态'], 400);
        }

        $user = User::find(auth('api')->user()->id);
        $user_info = UserInfo::where('user_id', $user->id)->first();
        if($user_info){
            # 管理员没有userinfo
            if($user_info->charge_status == '月租收费'){
                # 判断过期时间
                if(strtotime($user_info->expire_time) < time()){
                    $data['description'] = '账户过期';
                    $data['user_id'] = auth('api')->user()->id;
                    UserLog::create($data);
                    return error('', 401, '账户过期');
                }
            }
        }

        $brower = $_SERVER['HTTP_USER_AGENT'];
        $m_token =  'Bearer ' . $token;
        $user->last_login_ip =request()->getClientIp();
        $user->last_login_time = now();
        $user->remember_token = sha1($brower . $m_token);
        $user->save();
        UserInfo::where('user_id', $user->id)->increment('loginnum', 1);

        # 4. 登陆成功插入日志
        $data['description'] = '用户登陆成功';
        $data['user_id'] = auth('api')->user()->id;
        UserLog::create($data);



        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        # 直接获取token的字符串(没有Bearer)
        # $token = JWTAuth::getToken();
//        JWTAuth::parseToken()->invalidate();  # token失效
        # $info = auth('api')->check(); # 验证token是否有效, true or false

        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $info = auth('api')->logout();

        return success($info, 200, '退出成功');
    }

    /**
     * Refresh a token.
     * 刷新token，如果开启黑名单，以前的token便会失效。
     * 值得注意的是用上面的getToken再获取一次Token并不算做刷新，两次获得的Token是并行的，即两个都可用。
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
//        return response()->json([
//            'access_token' => $token,
//            'token_type' => 'bearer',
//            'expires_in' => auth('api')->factory()->getTTL() * 60
//        ]);


        $data = [
            'token'         => 'Bearer ' . $token,
            'token_type'    => 'bearer',
            'expires_in'    => auth('api')->factory()->getTTL() * 60
        ];

        return success($data, 200, '登陆成功');
    }
}
