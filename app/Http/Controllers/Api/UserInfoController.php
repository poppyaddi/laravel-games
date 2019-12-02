<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserInfoController extends Controller
{
    //
    public function index(Request $request)
    {

        $name           = $request->name;
        $nickname       = $request->nickname;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $status         = User::get_status($request->status);
        $order          = get_real_order($request->sortOrder);

        $query =        UserInfo::join('users', 'users.id', '=', 'userinfo.user_id')
                        ->when($name, function($query, $name){
                            return $query->where('users.name', $name);
                        })
                        ->when($nickname, function($query, $nickname){
                            return $query->where('userinfo.nickname', $nickname);
                        })
                        ->whereIn('status', $status);

        $data['total']  = $query->count();

        $data['data']   = $query
                        ->orderBy('userinfo.'. $sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->select('userinfo.id', 'userinfo.user_id', 'users.name', 'userinfo.charge_status', 'userinfo.save_device', 'userinfo.expire_time', 'users.status', 'userinfo.money', 'userinfo.nickname', 'pass_store')
                        ->get();

        return success($data);
    }

    public function detail(Request $request)
    {
        $data = UserInfo::join('users', 'users.id', '=', 'userinfo.user_id')
                ->where('userinfo.id', $request->id)->select('userinfo.id', 'users.name', 'userinfo.nickname', 'userinfo.charge_status', 'userinfo.save_device', 'userinfo.admin', 'userinfo.pass_store', 'userinfo.expire_time', 'userinfo.money')->first();
        return success($data);
    }

    public function update(Request $request)
    {

        $data = UserInfo::handleChargeStatus($request->all());
        $data = UserInfo::handleAdmin($data);
        $data = UserInfo::handlePassStore($data);
        $data = UserInfo::handleSaveDevice($data);
        $data['expire_time'] = date('Y-m-d', strtotime($data['expire_time']) + 8*3600);
        Arr::pull($data, 'name');
        $info = UserInfo::where('id', $data['id'])->update($data);
        return success($info, 200, '修改成功');
    }

    public function pay_reset_password(Request $request)
    {
        $info = UserInfo::where('id', $request->id)
            ->update(['pay_pass'=>sha1(Config::get_value('pay_pass'))]);
        return success($info, 200, '重置成功');
    }

}
