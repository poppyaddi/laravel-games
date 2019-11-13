<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\AuthController as Controller;
use App\Models\User;
use Illuminate\Http\Request;
use PDOException;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        parent::__construct();
    }

    //


    public function store(Request $request)
    {
        $password           = '123456'; # 密码后期从数据库里查询
        $data               = $request->all();
        $data['password']   = $password;

        try {
            $info = User::create($data);
        } catch (PDOException $e) {
            return error('', 400, '添加失败,查看用户名是否重复');
        }
        return success($info, 201, '添加成功');
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
        $password       = '123456';
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
