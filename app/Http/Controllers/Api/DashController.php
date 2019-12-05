<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buy;
use App\Models\SaleLog;
use App\Models\Son;
use App\Models\Store;
use App\Models\UserInfo;
use App\Models\WithdrawFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashController extends Controller
{
    //

    public function index()
    {
        $user = auth('api')->user();

        if($user->role_id == 1){
            $store = DB::select("select sum(if(zh_stores.status=1, 1, 0)) as s1, sum(if(zh_stores.status=2, 1, 0)) as s2, sum(if(zh_stores.status=8, 1, 0)) as s8, sum(if(status, 1, 0)) as total from zh_stores");

            $data['s1'] = $store[0]->s1;
            $data['s2'] = $store[0]->s2;
            $data['s8'] = $store[0]->s8;
            $data['total'] = $store[0]->total;


            $data['available_money'] = UserInfo::sum('money');
            $sale_money = DB::select("select sum(unit * price) as sale_money from zh_sale_logs");
//            dump($sale_money);die;

            $data['sale_money'] = $sale_money[0]->sale_money;

            # 总的求购金额为两个加一块
            $want_to_buy_money_prompt = DB::select("select sum(unit * unit_price) as want_to_buy_money_prompt from zh_prompt_afford");

            $want_to_buy_money_delay = DB::select("select sum(default_unit * unit_price) as want_to_buy_money_delay from zh_afford");
            $data['want_to_buy'] = $want_to_buy_money_prompt[0]->want_to_buy_money_prompt + $want_to_buy_money_delay[0]->want_to_buy_money_delay;

            # 冻结金额为userinfo, buy, afford表所有冻结金额的和
            $fro_user = UserInfo::sum('fro_money');  # number

            $fro_buy = Buy::sum('fro_money');  # number
            $fro_afford = DB::select("select sum(fro_buy_money + fro_self_money) as fro_money from zh_afford");

            $data['fro_money'] = $fro_user + $fro_buy + $fro_afford[0]->fro_money;

            # 提现手续费
            $data['withdraw_money'] = WithdrawFee::sum('money');  # number



            # 出库手续费
            $data['output_money'] = '还没有写';

            # 出售手续费 求购手续费 (包括求供双方)
            $sale_fee = DB::select("select sum(if(status=1, money, 0)) as sale_fee, sum(if(status=2 or status=3, money, 0)) as buy_afford_fee from zh_trans_fee");

            $data['sale_fee'] = $sale_fee[0]->sale_fee;
            $data['buy_afford_fee'] = $sale_fee[0]->buy_afford_fee;

            # 入库统计
            $table = DB::select("select zh_stores.id, sum(zh_prices.money) as money, count(*) as number, date(zh_stores.created_at) as time  from zh_stores join zh_prices on zh_stores.price_id=zh_prices.id group by date(zh_stores.created_at) order by date(zh_stores.created_at) desc limit 10");

            $res['field'] = $data;
            $res['table'] = $table;

            return success($res);
        } else{
            $son_ids = Son::where('user_id', $user->id)->pluck('id')->toArray();
            $son_ids = '(' . implode(',', $son_ids) . ')';

            # 个人用户需要计算子账户与主账户
            $store = DB::select("select sum(if(zh_stores.status=1, 1, 0)) as s1, sum(if(zh_stores.status=2, 1, 0)) as s2, sum(if(zh_stores.status=8, 1, 0)) as s8, sum(if(status, 1, 0)) as total from zh_stores where (owner_user_id = " . $user->id. " and user_type = 1) or (owner_user_id in " . $son_ids .  ") and user_type = 2");

            $data['s1'] = $store[0]->s1;
            $data['s2'] = $store[0]->s2;
            $data['s8'] = $store[0]->s8;
            $data['total'] = $store[0]->total;


            $data['available_money'] = UserInfo::where('user_id', $user->id)->sum('money');
            $sale_money = DB::select("select sum(unit * price) as sale_money from zh_sale_logs where user_id = :id", ['id'=>$user->id]);
//            dump($sale_money);die;

            $data['sale_money'] = $sale_money[0]->sale_money;

            # 总的求购金额为两个加一块
            $want_to_buy_money_prompt = DB::select("select sum(unit * unit_price) as want_to_buy_money_prompt from zh_prompt_afford where user_id = :id", ['id'=>$user->id]);

            $want_to_buy_money_delay = DB::select("select sum(default_unit * unit_price) as want_to_buy_money_delay from zh_afford where user_id = :id", ['id'=>$user->id]);
            $data['want_to_buy'] = $want_to_buy_money_prompt[0]->want_to_buy_money_prompt + $want_to_buy_money_delay[0]->want_to_buy_money_delay;

            # 冻结金额为userinfo, buy, afford表所有冻结金额的和
            $fro_user = UserInfo::where('user_id', $user->id)->sum('fro_money');  # number

            $fro_buy = Buy::sum('fro_money');  # number
            $fro_afford = DB::select("select sum(fro_buy_money + fro_self_money) as fro_money from zh_afford where user_id = :id", ['id'=>$user->id]);

            $data['fro_money'] = $fro_user + $fro_buy + $fro_afford[0]->fro_money;

            # 提现手续费
            $data['withdraw_money'] = WithdrawFee::where('user_id', $user->id)->sum('money');  # number



            # 出库手续费
            $data['output_money'] = '还没有写';

            # 出售手续费 求购手续费 (包括求供双方)
            $sale_fee = DB::select("select sum(if(status=1, money, 0)) as sale_fee, sum(if(status=2 or status=3, money, 0)) as buy_afford_fee from zh_trans_fee where user_id = :id", ['id'=>$user->id]);

            $data['sale_fee'] = $sale_fee[0]->sale_fee;
            $data['buy_afford_fee'] = $sale_fee[0]->buy_afford_fee;

            # 入库统计
            # 入库统计只统计子账户的
            $son_ids = Son::where('user_id', $user->id)->pluck('id')->toArray();
            $son_ids = '(' . implode(',', $son_ids) . ')';
            $table = DB::select("select zh_stores.id, sum(zh_prices.money) as money, count(*) as number, date(zh_stores.created_at) as time  from zh_stores join zh_prices on zh_stores.price_id=zh_prices.id where zh_stores.input_user_id in " . $son_ids .  " group by date(zh_stores.created_at) order by date(zh_stores.created_at) desc limit 10");

            $res['field'] = $data;
            $res['table'] = $table;

            return success($res);
        }
    }
}
