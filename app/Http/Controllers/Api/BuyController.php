<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Afford;
use App\Models\Buy;
use App\Models\Config;
use App\Models\Fee;
use App\Models\PromptAfford;
use App\Models\Store;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuyController extends Controller
{
    //
    /**
     * 发布求购，添加到数据库
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * 将所有人的求购信息展示到求购列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * 修改求购信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
        $info1      = UserInfo::where('user_id', $user->id)
                    ->increment('money', $fro_money + $fro_money_fee);

        # 1.2 修改冻结的手续费

        $info2      = UserInfo::where('user_id', $user->id)
                    ->decrement('fro_money', $fro_money_fee);

        if($info && $info1){
            DB::commit();
            return success($info, 200, '修改成功');
        } else{
            DB::rollBack();
            return error('', 400, '修改失败');
        }

    }

    /**
     * 将我的求购信息下架
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * 供货人有足够的凭证，可以直接供货
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
         $info1         = Store::whereIn('id', $afforded)  # 转移凭证
                        ->update(['owner_user_id'=>$buy->user_id]);
        $trans_fee      = Config::get_value('pre_store_fee');
        # 给凭证提供者资金
        $info2          = UserInfo::where('user_id', $user->id)
                        ->increment('money', $unit * $buy->unit_price * (1 - $trans_fee));

        # 扣除购买者的等价的钱
        $info3          = Buy::where('id', $id)
                        ->update(['status'=>$status, 'fro_money'=>$buy->fro_money - $unit * $buy->unit_price, 'unit'=>$buy->unit - $unit]);

        # 扣除购买者的保证金
        $info4 = UserInfo::where('user_id', $buy->user_id)->decrement('fro_money', $trans_fee * $unit * $buy->unit_price);

        # 即时供货写入即时日志
        $prompt['user_id'] = $user->id;
        $prompt['buy_id'] = $id;
        $prompt['unit'] = $unit;
        $prompt['unit_price'] = $buy->unit_price;
        $info5 = PromptAfford::create($prompt);

        # 添加卖家手续费日志
        $fee['user_id'] = $buy->user_id;
        $fee['money'] = $trans_fee * $unit * $buy->unit_price;
        $fee['description'] = '用户求购凭证，扣除求购者手续费';
        $info6 = Fee::create($fee);

        # 添加供货者手续费日志
        $fee['user_id'] = $user->id;
        $fee['money'] = $trans_fee * $unit * $buy->unit_price;
        $fee['description'] = '用户求购凭证，扣除提供凭证用户的手续费';
        $info7 = Fee::create($fee);


        if($info1 && $info2 && $info3 && $info4 && $info5 && $info6 && $info7){
            DB::commit();
            return success('', 200, '供货成功');
        } else{
            DB::rollBack();
            return error('', 400, '供货失败');
        }


    }

    /**
     * 求购列表展示我发布的求购(我的求购modal)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function qiugou_me(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $user_id = auth('api')->user()->id;

        $query          = Buy::whereIn('status', [1, 2])
                        ->when($user_id, function($query, $user_id){
                            return $query->where('user_id', $user_id);
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

    /**
     * 展示我的求购(预供货)列表信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me_pre(Request $request)
    {
        $game_id = $request->game_id;
        $price_id = $request->price_id;
        $status = $request->status;
        $order_num = $request->order_num;
        $start_time = $request->start_time;
        $end_time = $request->end_time;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        if(!$status){
            $status = $request->type ? null : [1, 2];
        } else{
            $status = [$status];
        }

        # 管理员查看所有，非管理员看自己的
        $user = auth('api')->user();
        $user_id = null;
        if($user->role_id != 1){
            $user_id = $user->id;
        }

        $query          = Buy::when($status, function($query, $status){
            return $query->whereIn('status', $status);
        })
            ->when($game_id, function($query, $game_id){
                return $query->where('game_id', $game_id);
            })
            ->when($price_id, function($query, $price_id){
                return $query->where('price_id', $price_id);
            })
            ->when($order_num, function($query, $order_num){
                return $query->where('order_num', $order_num);
            })
            ->when($status, function($query, $status){
                return $query->where('status', $status);
            })
            ->when($start_time, function($query, $start_time){
                return $query->where('created_at', '>', $start_time);
            })
            ->when($end_time, function($query, $end_time){
                return $query->where('created_at', '<', $end_time);
            })
            ->when($user_id, function($query, $user_id){
                return $query->where('user_id', $user_id);
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

    /**
     * 在我的求购(预供货)列表中，查看都有哪些人给我预先供货
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function afford_user(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);


        $query          = Afford::where('buy_id', $request->id)

            ->with(['user'=>function($query){
                return $query->select('user_id', 'nickname');
            }])
            ->select('id', 'user_id', 'unit', 'default_unit', 'created_at', 'unit_price', 'created_at', 'status');

        $data['total']  = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();

        return success($data, 200);
    }

    /**
     * 用户抢单成功后，未在指定时间内完成供货，扣除保证金
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function punish(Request $request)
    {
        $id = $request->id;  #
        $afford = Afford::find($id);

        # 0. 判断是否订单是否过期
        $create_time = strtotime($afford->created_at);
        $expire_time = Config::get_value('affore_store_expire_time') * 24 * 3600;
        if(time() < $create_time + $expire_time){
            # 如果过期
            return error('', 400, '订单未过期');
        }

        DB::beginTransaction();
        # 1 将供货者的保证金瓜分掉
        $for_buy = Config::get_value('afford_expire_punish_fee') * $afford->fro_self_money; # 供货者的保证金中分给求购者的金额
        $for_admin = $afford->fro_self_money - $for_buy;  # 最后都给总账户或者手续费日志
        # 1.1 将冻结的求购者的金额还给求购者，求购者的冻结手续费还给原账户
        $buy_user_id = Buy::where('id', $afford->buy_id)->first()->user_id; # 求购者id
        $bond = Config::get_value('pre_store_fee') * $afford->unit * $afford->unit_price; # 求购者冻结的交易手续费
        $info1 = UserInfo::where('user_id', $buy_user_id)->increment('money', (1 + Config::get_value('pre_store_fee')) * $afford->unit * $afford->unit_price + $for_buy);

        $info2 = UserInfo::where('user_id', $buy_user_id)->decrement('fro_money', $bond); # 求购者去掉等额的冻结交易费

        $info3 = Afford::where('id', $id)->update(['fro_buy_money'=>0, 'fro_self_money'=>0, 'status'=>4]);

        if($info1 && $info2 && $info3){
            DB::commit();
            return success('', 200, '惩罚成功');
        } else{
            return error('', 400, '惩罚失败');
        }
    }

    public function afford_user_prompt(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);


        $query          = PromptAfford::where('buy_id', $request->id)

            ->with(['user'=>function($query){
                return $query->select('user_id', 'nickname');
            }])
            ->select('id', 'user_id', 'unit', 'created_at', 'unit_price', 'created_at');

        $data['total']  = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();

        return success($data, 200);
    }

}
