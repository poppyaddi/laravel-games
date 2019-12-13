<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Afford;
use App\Models\Buy;
use App\Models\Config;
use App\Models\Fee;
use App\Models\Store;
use App\Models\TransFee;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffordController extends Controller
{
    //

    public function index(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $user = auth('api')->user();

        $where = null;
        # 管理员看所有，自己只能看自己
        if($user->role_id != 1){
            $where = $user->id;
        }

        $query          = Afford::when($where, function($query, $where){
                            return $query->where('user_id', $where);
                        })
                        ->with(['user'=>function($query){
                            return $query->select('user_id', 'nickname');
                        }])
                        ->with(['price'=>function($query){
                            return $query->with(['game'=>function($query){
                                return $query->select('id', 'name');
                            }])
                                ->select('id', 'gold', 'game_id');
                        }]);

        $data['total']  = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();

        return success($data, 200);
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

        $buy = Buy::find($id);

        $status = $buy->unit == $unit ? 3 : 2;  # 预先供货

        DB::beginTransaction();
        # 1. 将购买者的等额资金转移到供货者
        $earn = $unit * $buy->unit_price;
        $info1 = Buy::where('id', $id)->update(['status'=>$status, 'fro_money'=>$buy->fro_money - $earn, 'unit'=>$buy->unit - $unit]);

        $data['user_id'] = $user->id;
        $data['buy_id'] = $id;
        $data['unit'] = $unit;
        $data['default_unit'] = $unit;
        $data['unit_price'] = $buy->unit_price;
        $data['fro_buy_money'] = $earn;
        $data['fro_self_money'] = $earn * Config::get_value('pre_afford_fee');
        $data['price_id'] = $buy->price_id;
        $info2 = Afford::create($data);

        # 2. 供货者减去等额保证金
        $info3 = UserInfo::where('user_id', $user->id)->decrement('money', $data['fro_self_money']);

        $info1 && $info2 && $info3 ? DB::commit() : DB::rollBack();

        return success('', 200, '预供成功');
    }

    public function finish(Request $request)
    {
        $user = auth('api')->user();
        # 0. 获取被供货信息
        $afford = Afford::find($request->id);

        # 1. 获取供货人该凭证个数
        $where = [
            ['user_type', '=', 1],
            ['status', '=', 1],
            ['owner_user_id', '=', $user->id],
            ['price_id', '=', $afford->price_id]
        ];
        $count = Store::where($where)->count();

        if($count >= $afford->unit){
            return success('', 200, '凭证充足，是否供货？');
        } elseif($count == 0){
            return success('', 200, '凭证数量为0');
    }
        else{
            return success('', 200, '凭证剩余' . $count . '个, 不足' . $afford->unit . '个, 是否供货？');
        }
    }

    public function done(Request $request)
    {
        $user = auth('api')->user();
        # 0. 获取被供货信息
        $afford = Afford::find($request->id);

        # 1. 获取供货人该凭证个数
        $where = [
            ['user_type', '=', 1],
            ['status', '=', 1],
            ['owner_user_id', '=', $user->id],
            ['price_id', '=', $afford->price_id]
        ];

        # 2. 查找被转移的凭证
        $store_ids = Store::where($where)->pluck('id')->toArray();
        $transfer_store_ids = $afford->unit > count($store_ids) ? $store_ids : array_slice($store_ids, 0, $afford->unit);

        $status = count($store_ids) >= $afford->unit ? 3 : 2;

        # 3. 转移凭证并算账
        DB::beginTransaction();
        $pre_store_fee = Config::get_value('pre_store_fee');
        $transfer_user_id = Buy::where('id', $afford->buy_id)->first()->user_id;
        $info1 = Store::whereIn('id', $transfer_store_ids)->update(['owner_user_id'=>$transfer_user_id]);  # 转移凭证

        # 3.2 减去保证金并减去预购者的预付款
        $info2 = Afford::where('id', $request->id)->update(['fro_buy_money'=>$afford->fro_buy_money - $afford->unit_price * count($transfer_store_ids), 'fro_self_money'=>$afford->fro_self_money - $pre_store_fee * $afford->unit_price * count($transfer_store_ids), 'status'=>$status, 'unit'=>$afford->unit - count($transfer_store_ids) ]);

        # 3.3 将减去的保证金给资金账户, 将供货者扣除手续费之后的钱给供货者账户
        $bond =  $pre_store_fee * $afford->unit_price * count($transfer_store_ids);  # 减去的保证金

        $info3 = UserInfo::where('user_id', $user->id)->increment('money', $afford->unit_price * count($transfer_store_ids) * (1 - $pre_store_fee) + $bond);

        # 3.4 求购者用户冻结金额的手续费减去

        $info4 = UserInfo::where('user_id', $transfer_user_id)->decrement('fro_money', $bond);

        # 3.5 添加求购者手续费日志
        $order_num = Buy::where('id', $afford->buy_id)->first()->order_num;
        $fee['user_id'] = $transfer_user_id;
        $fee['money'] = $afford->unit_price * count($transfer_store_ids) * $pre_store_fee;
        $fee['description'] = '用户发布预购，供货时扣除发布者手续费';
        $fee['order_num'] = $order_num;
        $fee['status'] = 2;
        $info5 = TransFee::create($fee);

        # 3.6 提供凭证者添加手续费日志
        $fee['user_id'] = $user->id;
        $fee['money'] = $afford->unit_price * count($transfer_store_ids) * $pre_store_fee;
        $fee['description'] = '用户发布预购，供货时扣除凭证提供者手续费';
        $fee['order_num'] = $order_num;
        $fee['status'] = 3;
        $info6 = TransFee::create($fee);

        # 添加供货日志

        if($info1 && $info2 && $info3 && $info4 && $info5 && $info6){
            DB::commit();
            return success('', 200, '供货成功');
        } else{
            DB::rollBack();
            return error('', 400, '供货失败');
        }

    }



}
