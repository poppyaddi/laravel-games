<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;
use App\Models\Config;


class UserController extends Controller
{


    //


    public function store(Request $request)
    {
        $data['name']               = $request->name;
        $data['role_id']            = $request->role_id;
        $configs                    = Config::pluck('value', 'key');
        $data['password']           = $configs['password'];
        $userInfo['pay_pass']       = $configs['pay_pass'];
        $t                          = time() + $configs['expire_time'] * 3600 * 24;
        $userInfo['expire_time'] = date('Y-m-d H:i:s', $t);
        $userInfo['save_device']    = $configs['save_device'];
        $userInfo['admin']          = $configs['admin'];
        $userInfo['pass_store']     = $configs['pass_store'];
        $userInfo['charge_status']  = $configs['charge_status'];
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
                            $query->where('name', $name);
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
}
