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
                            return $query->where('users.name', 'like', '%' . $name . '%');
                        })
                        ->when($nickname, function($query, $nickname){
                            return $query->where('userinfo.nickname', 'like', '%' . $nickname . '%');
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
        $data['expire_time'] = $data['charge_status'] == 1 ? date('Y-m-d', strtotime($data['expire_time']) + 8*3600) : '2060-12-31';
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

    public function select()
    {
        $data = UserInfo::select('user_id', 'nickname')->get();
        return success($data);
    }

    public function judge_status()
    {
        $user = auth('api')->user();

        $info = UserInfo::where('user_id', $user->id)->first();
        $data['nickname_change_times'] = $info->nickname_change_times;
        $data['nickname'] = $info->nickname;
        $data['money'] = Config::get_value('nickname_modify_money');
        return success($data);
    }

    public function reset_nickname(Request $request)
    {
        $new_nickname = $request->new_nickname;

        # 判断该账号是否需要扣费
        $user = auth('api')->user();

        $info = UserInfo::where('user_id', $user->id)->first();
        if($info->nickname_change_times == 0){
            # 不需要扣费
            $info->nickname = $new_nickname;
            $info = UserInfo::where('user_id', $user->id)->update(['nickname'=>$new_nickname, 'nickname_change_times'=>$info->nickname_change_times + 1]);
            return success($info, 200, '修改成功');
        } else{
            # 需要扣费
            $bond = Config::get_value('nickname_modify_money');
            # 判断账户余额是否足够
            if($info->money - $bond < 0){
                return error('', 400, '余额不足');
            }

            UserInfo::where('user_id', $user->id)->update(['nickname'=>$new_nickname, 'money'=>$info->money - $bond, 'nickname_change_times'=>$info->nickname_change_times + 1]);
            return success('', 200, '修改成功');
        }

    }

    public function member(Request $request)
    {
        $user = auth('api')->user();
        # 月租用户则续费
        $time = $request->time;
        # 判断用户当前套餐状态
        $info = UserInfo::where('user_id', $user->id)->first();
        # 扣除月租费用
        $fee = Config::get_value('base_member_price');

        # 不管什么用户，首先判断余额是否充足
        switch ($time){
            case 2:
                $times = Config::get_value('two_month_discount');
                break;
            case 3:
                $times = Config::get_value('three_month_discount');
                break;
            case 6:
                $times = Config::get_value('six_month_discount');
                break;
            default:
                $times = 1;
        }

        $fee = $fee * $time * $times;
        # 查看用户账户余额是否足够
        if($info->money - $fee < 0){
            return error('', 400, '账户可用余额不足, 请联系管理员充值');
        }

        if($info->charge_status != "月租收费"){
            # 不是月租用户首先修改月租状态

            $data = ['charge_status' => 1, 'expire_time' => date('Y-m-d H:i:s', strtotime('+ ' . $time .  ' month')), 'money' => $info->money - $fee];
            try{
                $flag = UserInfo::where('user_id', $user->id)->update($data);
            } catch (\PDOException $e){
                return error('', 400, '购买失败');
            }
            return success('', 200, '购买成功');

        }
        # 如果是月租用户则是续费
        try{

            $data = ['money' => $info->money - $fee, 'expire_time' => date('Y-m-d H:i:s', strtotime("$info->expire_time + $time month "))];
            $flag = UserInfo::where('user_id', $user->id)->update($data);
            return success($flag, 200, '套餐购买成功');
        } catch (\PDOException $e){
            return error('', 400, '套餐购买失败');
        }
    }



}
