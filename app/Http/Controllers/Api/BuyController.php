<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buy;
use App\Models\Config;
use App\Models\Store;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuyController extends Controller
{
    //
    public function store(Request $request)
    {
        $data   = $request->all();

        $user   = auth('api')->user();

        # 0. 判断该账户是否有这么多余额
        $user_info = UserInfo::where('user_id', $user->id)->first();

        if($user_info->money < $data['unit'] * $data['unit_price']){
            return error('', 400, '余额不足');
        }

        $data['user_id'] = $user->id;

        # 1. 生成订单号
        $data['order_num']      = $this->get_order();
        $data['default_unit']   = $request->unit;

        # 2. 保存数据库并扣除等同金额的钱
        DB::beginTransaction();
        $transfer_fee = Config::get_value('pre_store_fee') * $data['unit'] * $data['unit_price'];  # 发布者也要扣除手续费
        $info1 = UserInfo::where('user_id', $user->id)->decrement('money', $data['unit'] * $data['unit_price'] + $transfer_fee);
        $data['fro_money'] = $data['unit'] * $data['unit_price'];
        $info2 = Buy::create($data);

        # 3. 将扣除的手续费添加到用户冻结金额
        $info3 = UserInfo::where('user_id', $user->id)->increment('fro_money', $transfer_fee);

        if($info1 && $info2 && $info3){
            DB::commit();
            return success('', 200, '添加成功');
        } else{
            return error('', 400, '账户余额不足');
        }


    }

    protected function get_order(){
        $order_id_main  = date('YmdHis') . rand(10000000,99999999);
        $order_id_len   = strlen($order_id_main);
        $order_id_sum   = 0;
        for($i=0; $i<$order_id_len; $i++){
            $order_id_sum += (int)(substr($order_id_main,$i,1));
        }
        return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $game_id         = $request->game_id;
        $price_id       = $request->price_id;
        $user_id        = $request->user_id;
        $status         = $request->status;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $query          = Buy::whereIn('status', [1, 2])
                        ->when($game_id, function($query, $game_id){
                            return $query->where('game_id', $game_id);
                        })
                        ->when($price_id, function($query, $price_id){
                            return $query->where('price_id', $price_id);
                        })
                        ->when($user_id, function($query, $user_id){
                            return $query->where('user_id', $user_id);
                        })
                        ->when($status, function($query, $status){
                            return $query->where('status', $status);
                        })
                        ->with(['user'=>function($query){
                            return $query->select('user_id', 'nickname');
                        }])
                        ->with(['game'=>function($query){
                            return $query->select('id', 'name');
                        }])
                        ->with(['price'=>function($query){
                            return $query->select('id', 'gold');
                        }]);

        $data['total']  = $query->count();
        $data['data']   = $query
                        ->orderBy($sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();

        return success($data, 200);

    }

    public function update(Request $request)
    {
        $id         = $request->id;

        $user       = auth('api')->user();

        # 只有自己或管理员可修改
        $buy   = Buy::find($id);

        if($user->id != $buy->user_id && $user->role_id != 1){
            return error('', 400, '无权限');
        }

        # 0. 验证交易密码
        $user_info  = UserInfo::where('user_id', $user->id)->first();
        if (sha1($request->password) != $user_info->pay_pass){
            return error('', 400, '交易密码错误');
        }
        # 1. 修改订单
        # 1.1 将冻结的金额
        DB::beginTransaction();
        $fro_money = $buy->fro_money - $request->unit_price * $request->unit; # 冻结的购买费用
        $fro_money_fee = Config::get_value('pre_store_fee') * $buy->unit * $buy->unit_price - Config::get_value('pre_store_fee') * $request->unit_price * $request->unit; # 冻结的手续费
        $info       = Buy::where('id', $request->id)
                    ->update(['unit_price'=>$request->unit_price, 'unit'=>$request->unit, 'fro_money'=>$request->unit_price * $request->unit]);
        $info1 = UserInfo::where('user_id', $user->id)->increment('money', $fro_money + $fro_money_fee);

        # 1.2 修改冻结的手续费

        $info2 = UserInfo::where('user_id', $user->id)->decrement('fro_money', $fro_money_fee);

        if($info && $info1){
            DB::commit();
            return success($info, 200, '修改成功');
        } else{
            DB::rollBack();
            return error('', 400, '修改失败');
        }

    }

    public function down(Request $request)
    {
        # 判断权限，只有自己或者管理员才能下架
        $id     = $request->id;
        $user   = auth('api')->user();
        $buy   = Buy::find($id);

        if($user->id != $buy->user_id && $user->role_id != 1){
            return error('', 400, '无权限');
        }

        # 把钱还原并修改状态
        DB::beginTransaction();
        $bond = Config::get_value('pre_store_fee') * $buy->unit * $buy->unit_price; # 冻结的手续费
        $info1 = UserInfo::where('user_id', $user->id)->increment('money', $buy->fro_money + $bond);
        $info2 = Buy::where('id', $id)->update(['status'=>4, 'fro_money'=>0]);

        # 减掉等同的冻结金额

        $info3 = UserInfo::where('user_id', $user->id)->decrement('fro_money', $bond);

        if($info1 && $info2 && $info3){
            DB::commit();
            return success('', 200, '下架成功');
        } else{
            DB::rollBack();
            return error('', 400, '下架失败');
        }


    }

    public function afford(Request $request)
    {

        $unit       = $request->unit;  # 供货数量
        $pay_pass   = $request->password;  # 支付密码
        $id         = $request->id;
        $user       = auth('api')->user();

        # 0. 验证交易密码
        $user_info  = UserInfo::where('user_id', $user->id)->first();
        if (sha1($pay_pass) != $user_info->pay_pass){
            return error('', 400, '交易密码错误');
        }

        # 1. 判断库存是否足够
        $buy = Buy::find($id);
        $where = [
            ['user_type', '=', 1],
            ['owner_user_id', '=', $user->id],
            ['status', '=', '1'],
            ['price_id', '=', $buy->price_id]
        ];
        $count = Store::where($where)->pluck('id')->toArray();
        if(count($count) < $unit){
            return error('', 400, '库存不足');
        }

        $status = $buy->unit == $unit ? 3 : 2;

        $afforded = array_slice($count, 0, $unit);  # 将这些凭证转给别人

        DB::beginTransaction();
                $info1  = Store::whereIn('id', $afforded)  # 转移凭证
                        ->update(['owner_user_id'=>$buy->user_id]);
        $trans_fee      = Config::get_value('trans_fee');
        # 给凭证提供者资金
        $info2          = UserInfo::where('user_id', $user->id)
                        ->increment('money', $unit * $buy->unit_price * (1 - $trans_fee));

        # 扣除购买者的等价的钱
        $info3          = Buy::where('id', $id)
                        ->update(['status'=>$status, 'fro_money'=>$buy->fro_money - $unit * $buy->unit_price, 'unit'=>$buy->unit - $unit]);

        $info1 && $info2 && $info3 ? DB::commit() : DB::rollBack();

        return success('', 200, '供货成功');
    }

}
