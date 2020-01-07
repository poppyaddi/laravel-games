<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Port\Rsa1024Controller;

class AuthController extends Rsa1024Controller
{
    //
//    public function __construct()
//    {
//        $this->middleware('auth:port', ['except' => ['login']]);
//        parent::__construct();
//    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
         # 处理参数
        $username   = $this->param('username');
        $password   = $this->param('password');
        $client     = $this->param('client');
        $version    = $this->param('version');
        $device_id  = $this->param('device_id');
        $type       = $this->param('type');

        if (empty($username)) {
            return $this->RSA_private_encrypt(err('username length is 0'));
        }

        if (empty($password)) {
            return $this->RSA_private_encrypt(err('password length is 0'));
        }

        if (empty($client)) {
            return $this->RSA_private_encrypt(err('client length is 0'));
        }

        if (empty($version)) {
            return $this->RSA_private_encrypt(err('version length is 0'));
        }

        if (empty($device_id)) {
            return $this->RSA_private_encrypt(err('device_id length is 0'));
        }

        if (empty($type)) {
            return $this->RSA_private_encrypt(err('type length is 0'));
        }

        $credentials = ['name'=>$username, 'password'=>$password, 'status'=>1];

        # 插入日志
        $data = [
            'login_ip' => request()->getClientIp(),
            'type' => 2
        ];

        if (! $token = auth('port')->attempt($credentials)) {
            # 插入日志
            $data['description'] = '子账户登陆失败, 用户名"' . json_encode($credentials);
            UserLog::create($data);

            return $this->RSA_private_encrypt(err('请检查用户名或密码或账户状态'));
        }

        # 登录验证通过, 判断客户端类型
        $son_info = auth('port')->user();
        if($type == 'in'){
            if($son_info == '出库'){
                return $this->RSA_private_encrypt(err('账号类别不符合要求'));
            }
        } else{
            # 插件为出库
            if($son_info == '入库'){
                return $this->RSA_private_encrypt(err('账号类别不符合要求'));
            }
        }

        # 判断用户该设备是否为首次登录

        $map = [
            ['device', '=', $device_id],
            ['son_id', '=', auth('port')->user()->id]
        ];

        $device = Device::where($map)->first();

        $device_data = [
            'device' => $device_id,
            'son_id' => auth('port')->user()->id,
        ];

        if (!$device) {
            # 如果设备不存在则添加设备到数据库


            # 判断该子账户的主账户是否需要在后台启用设备
            $save_device = $this->parent()->userinfo->save_device;

            if($save_device == '无需启用'){
                $device_data['status'] = 2; # 1无效2启用3禁用
            }

            $device = Device::create($device_data);
        }
        #
        if($this->parent()->userinfo->save_device == '需启用' && $device['status'] != '启用'){
            return $this->RSA_private_encrypt(err('设备ID: ' . $device->id . ' 未授权, 请登录后台授权'));
        }

        $data['description'] = '子账户登陆成功, 用户名"' . json_encode($credentials);
        $data['user_id'] = auth('port')->user()->id;
        UserLog::create($data);

        # 中间件中检查账户是否过期(月租用户永不过期)
        $data = $this->respondWithToken($token);
        $res = ['data'=>$data, 'code'=>200, 'message'=>'登陆成功'];
        return $this->RSA_private_encrypt($res);
    }


    /**
     * @return string
     */
    public function logout()
    {
        auth('port')->logout();

        $data = ['data'=>'', 'code'=>200, 'message'=>'退出成功'];
        return $this->RSA_private_encrypt($data);
    }


    /**
     * @param $token
     * @return array
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('port')->factory()->getTTL() * 60
        ];
    }

}
