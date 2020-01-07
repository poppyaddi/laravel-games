<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Son;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;
use App\Models\Config;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{


    //


    public function store(Request $request)
    {
        $data['name']               = $request->name;
        $data['role_id']            = $request->role_id;
        $data['created_at']         = now();
        $configs                    = Config::pluck('value', 'key');
        $data['password']           = bcrypt($configs['password']);
        $userInfo['pay_pass']       = bcrypt($configs['pay_pass']);
        $userInfo['charge_status']  = $configs['charge_status'];
        $t                          = $configs['charge_status'] == 1 ? time() + $configs['expire_time'] * 3600 * 24 : '1700000000';
        $userInfo['expire_time'] = date('Y-m-d H:i:s', $t);
        $userInfo['save_device']    = $configs['save_device'];
        $userInfo['admin']          = $configs['admin'];
        $userInfo['pass_store']     = $configs['pass_store'];

        $userInfo['nickname']       = $configs['nickname_prefix'] . random_int(1, 999999);
//        return $userInfo;
//
//        try {
//            DB::transaction(function() use ($data){
//                $userInfo['user_id'] = User::insertGetId($data);
//                UserInfo::create($userInfo);
//            });
//        } catch (PDOException $e) {
//            return error('', 400, '添加失败,查看用户名是否重复');
//        }

        DB::transaction(function() use ($data, $userInfo){
            $userInfo['user_id'] = User::insertGetId($data);
            UserInfo::create($userInfo);
        });
        return success('', 201, '添加成功');
    }

    /**
     * 用户列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $name           = $request->name;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 10;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $status         = User::get_status($request->status);
        $order          = get_real_order($request->sortOrder);


        $query          = User::with(['role'=> function($query){
                            return $query->select('id', 'name');
                        }])
                        ->when($name, function($query, $name){
                            $query->where('name', 'like', '%' . $name . '%');
                        })
                        ->whereIn('status', $status);
        $data['total']  =$query->count();

        $data['data']   = $query
                        ->orderBy($sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();


        return success($data, 200);
    }

    public function status(Request $request)
    {
        $status = User::status($request->status);
        $info   = User::where(['id'=>$request->id])
                ->update(['status'=>$status]);
        return success($info, 200, '修改成功');
    }

    public function delete(Request $request)
    {
        $info = User::destroy($request->id);
        return success($info, 200, '删除成功');
    }

    public function reset_password(Request $request)
    {
        # 后期从数据库取数据
        $password       = Config::get_value('password');
        $user           = User::find($request->id);
        $user->password = $password;
        $info           = $user->save();
        return success($info, 200, '重置成功');
    }

    public function detail(Request $request)
    {
        $data = User::where(['id'=>$request->id])
                ->select('id', 'name', 'role_id')
                ->first();
        return success($data, 200);
    }

    public function update(Request $request)
    {
        $info = User::where(['id'=>$request->id])
                ->update($request->all());
        return success($info, 200, '修改成功');
    }

    public function tag_data()
    {
        $data['start'] = User::where(['status'=>1])
                        ->count();
        $data['total'] = User::count();
        return success($data, 200);
    }

    public function select(Request $request)
    {
        $user_type = $request->user_type;
        $user = auth('api')->user();
        # 判断请求账户类型
        if($user_type == 'user'){
            # 此时为主账户
            $map = '';
            if($user->role_id != 1){
                # 非管理员
                $map = [
                    ['id', '=', $user->id]
                ];
            }
            $data = User::when($map, function ($query, $map){
                return $query->where($map);
            })->where('id', '<>', 1)->select('id', 'name')->get();
        } elseif($user_type == 'son'){
            $map = '';
            if($user->role_id == 1){
                # 不是管理员, 只允许查看自己账户下的子账户

                $in = User::pluck('id');


            } else{
                $in = [$user->id];
            }
            $data = Son::whereIn('user_id', $in)->select('id', 'name')->get();
        } else{
            $data = null;
        }
        return success($data);
    }

    public function info(Request $request)
    {

        $data = User::where('id', auth('api')->user()->id)
                ->with(['userinfo'=>function($query){
                    return $query->select('id','user_id', 'nickname', 'money', 'fro_money', 'expire_time', 'charge_status');
                }])
                ->with(['role'=>function($query){
                    return $query->select('id', 'name');
                }])
                ->select('name', 'role_id', 'id', 'created_at', 'last_login_ip', 'last_login_time')
                ->first();

        return success($data);
    }

    public function user_reset_password(Request $request)
    {
        $old_password = $request->old_password;
        $new_password = $request->new_password;

        # 检查原始密码
        $user = auth('api')->user();
        $credentials = ['name'=>$user->name, 'password'=>$old_password];

        if($token = auth('api')->attempt($credentials)){
            # 验证通过
            $user = User::find($user->id);
            $user->password = $new_password;
            $user->save();
        } else{
            return error('', 400, '原密码错误');
        }
        return success($token, 200, '修改成功');
    }

    public function pay_update_password(Request $request)
    {
        $old_password = $request->old_password;
        $new_password = $request->new_password;

        # 检查原始密码
        $user = auth('api')->user();
        $user_info = UserInfo::where('user_id', $user->id)->first();


        if($user_info->pay_pass != sha1($old_password)){
            return error('', 400, '原密码错误');
        }
        $info = UserInfo::where('user_id', $user->id)->update(['pay_pass'=>sha1($new_password)]);

        return success($info, 200, '修改成功');
    }

    public function start_member_description(Request $request)
    {
        $description = Config::get_value('start_member_description');
        $base_price = Config::get_value('base_member_price');
        $one_month = ['duration' => '一月', 'price' => (int) $base_price];
        $two_month = ['duration' => '两月', 'price' => $base_price * 2 * Config::get_value('two_month_discount')];
        $three_month = ['duration' => '三月', 'price' => $base_price * 3 * Config::get_value('three_month_discount')];
        $six_month = ['duration' => '六月', 'price' => $base_price * 6 * Config::get_value('six_month_discount')];
        return success(['description' => $description, 'money' => ['one_month' => $one_month, 'two_month' => $two_month, 'three_month' => $three_month, 'six_month' => $six_month]]);
    }
}
