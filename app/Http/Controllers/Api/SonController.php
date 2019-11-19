<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Son;
use Illuminate\Http\Request;
use App\Models\Config;

class SonController extends Controller
{
    //
    public function store(Request $request)
    {
        # 获取配置文件中最大的子账户个数
        $max_son_num    = Config::get_value('max_son_num');
        # 获取当前用户的子账户数
        $account_num    = Son::get_account_nums();
        if($account_num >= $max_son_num){
            return success('', 400, '当前账户子账户个数已达到最大值');
        }

        $data = $request->all();
        $data['user_id'] = auth('api')->user()->id;
        try{
            $info = Son::create($data);
        } catch (\PDOException $e){
            return success('', 400, '子账户名称重复,请更换其他名称');
        }
        return success($info, 200, '添加成功');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $name           = $request->name;
        $son            = $request->son;
        $status         = $request->status;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        # 判断权限
        $user_id        = null;
        $role_id        = auth()->user()->role_id;
        if($role_id != 1){
            $user_id = auth()->user()->id;
        }

        $status         = !$status ? null : [$status];

        $query          = Son::when($son, function ($query, $son) {
                            return $query->where('sons.name', $son);
                        })->join('users', 'users.id', '=', 'sons.user_id')->when($name, function($query, $name){
                            return $query->where('users.name', $name);
                        })->when($status, function ($query, $status){
                            return $query->whereIn('sons.status', $status);
                        })->when($user_id, function($query, $user_id){
                            return $query->where('sons.user_id', $user_id);
        });
        $data['total'] = $query->count();
        $data['data']  = $query
                        ->orderBy('sons.' . $sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->select('sons.id', 'sons.name', 'sons.type', 'sons.status', 'sons.created_at', 'users.name as user')
                        ->get();

        return success($data, 200);
    }

    public function detail(Request $request)
    {
        $data = Son::where('id', $request->id)->select('name', 'type')->first();
        return success($data);
    }

    public function update(Request $request)
    {
        $son = Son::find($request->id);
        if($request->password){
            $son->password = $request->password;
        }
        $son->type = Son::handleType($request->type);
        $info = $son->save();
        return success($info, 200, '修改成功');
    }

    public function delete(Request $request)
    {
        $info = Son::destroy($request->id);
        return success($info, 200, '删除成功');
    }

    public function status(Request $request)
    {
        $son = Son::find($request->id);
        $son->status = Son::handleStatus($son->status);
        $info = $son->save();
        return success($info, 200, '修改成功');

    }
}
