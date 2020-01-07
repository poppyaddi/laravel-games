<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Fee;
use App\Models\Money;
use App\Models\UserInfo;
use App\Models\WithdrawFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

class MoneyController extends Controller
{
    //
    public function upload(Request $request)
    {
//        $path = $request->hasFile('wechat');
//        if($path){
//            return success('dddd');
//        }

        if($request->hasFile('wechat')){
            $path = $request->file('wechat')->store('public');
            # 如果有上传的文件则删除
            $wechat = Config::get_value('pay_wechat');
            Storage::delete($wechat);
            Config::where('key', 'pay_wechat')->update(['value'=>$path]);

            $path =  'storage/' . basename($path);
            $data = [[
                'uid' => -1,
                'name' => 'wechat',
                'status' => 'done',
                'url' =>  'http://' . $_SERVER['HTTP_HOST'] . '/' . $path
            ]];
            return success($data);
        } elseif($request->hasFile('ali')){
            $path = $request->file('ali')->store('public');

            # 删除原来的
            $ali = Config::get_value('pay_ali');
            Storage::delete($ali);

            Config::where('key', 'pay_ali')->update(['value'=>$path]);

            $path =  'storage/' . basename($path);

            $data = [
                [
                'uid' => -1,
                'name' => 'wechat',
                'status' => 'done',
                'url' =>  'http://' . $_SERVER['HTTP_HOST'] . '/' . $path
            ]
            ];
            return success($data);
        }

    }

    public function pic()
    {
        $data = [];
        $wechat = Config::get_value('pay_wechat');

        $we_path = 'storage/' . basename($wechat);
        $data['wechat'] = [
            [
                'uid' => -1,
                'name' => 'wechat',
                'status' => 'done',
                'url' =>  'http://' . $_SERVER['HTTP_HOST'] . '/' . $we_path
            ]
        ];

        $ali = Config::get_value('pay_ali');
        $a_path = '/storage/' . basename($ali);

        $data['ali'] = [
            [
                'uid' => -1,
                'name' => 'ali',
                'status' => 'done',
                'url' =>  'http://' . $_SERVER['HTTP_HOST'] . '/' . $a_path
            ]
        ];

        return success($data);
    }

    public function apply(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        # 判断是充值还是提现
        if($request->type == 1){
            # 充值
            $info = Money::create($data);
            return success($info, 200, '添加成功');
        } elseif ($request->type == 2){
            # 提现
            # 1. 判断账户余额是否足够
            $fee = Config::get_value('withdraw_fee');
            $user_info = UserInfo::where('user_id', $user->id)->first();
            $decrement = $request->money * (1 + $fee);
            if($user_info->money < $decrement){
                return error('', 400, '余额不足');
            }
            $data['type'] = 2;
            $data['fro_money'] = $decrement;
            DB::beginTransaction();
            # 账户内减去提现金额，并冻结等同金额
            $info1 = UserInfo::where('user_id', $user->id)->decrement('money', $decrement);
            $info2 = Money::create($data);
            if($info1 && $info2){
                DB::commit();
                return success('', 200, '申请添加成功');
            } else{
                DB::rollBack();
                return error('', 200, '申请添加失败');
            }

        }
    }

    public function index(Request $request)
    {
        $user_id = $request->user_id;
        $real_name = $request->real_name;
        $status = $request->status;
        $type = $request->type;
        $start_time = $request->start_time;
        $end_time = $request->end_time;
        $account = $request->account;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $user = auth('api')->user();

        $in = [];
        if($user->role_id != 1){
            $in = $user->id;
        }

        $query          = Money::with(['user'=>function($query){
                            return $query->select('id', 'name');
                        }])
                        ->when($in, function($query, $in){
                            return $query->where('user_id', $in);
                        })
                        ->when($user_id, function($query, $user_id){
                            return $query->where('user_id', $user_id);
                        })
                        ->when($real_name, function($query, $real_name){
                            return $query->where('real_name', 'like', '%' . $real_name . '%');
                        })
                        ->when($status, function($query, $status){
                            return $query->where('status', $status);
                        })
                        ->when($type, function($query, $type){
                            return $query->where('type', $type);
                        })
                        ->when($account, function($query, $account){
                            return $query->where('account', 'like', '%' . $account . '%');
                        })
                        ->when($start_time, function($query, $start_time){
                            return $query->where('created_at', '>', $start_time);
                        })
                        ->when($end_time, function($query, $end_time){
                            return $query->where('created_at', '<', $end_time);
                        })
                        ->select('id', 'user_id', 'money', 'type', 'real_name', 'account', 'account_type', 'created_at', 'description', 'status');


        $data['total'] = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();


        return success($data, 200);
    }

    public function status(Request $request)
    {
        # 判断是充值还是提现
        $money = Money::where('id', $request->id)->first();
        if($money->type == '充值'){
            if($request->status == '2'){
                # 通过审核
                DB::beginTransaction();
                $info1 = UserInfo::where('user_id', $money->user_id)->increment('money', $money->money);
                $info2 = Money::where('id', $request->id)->update(['status' => $request->status]);
                if($info1 && $info2){
                    DB::commit();
                } else{
                    DB::rollBack();
                }
            } elseif ($request->status == '3'){
                # 审核拒绝，只修改状态，不增加金额
                $info = Money::where('id', $request->id)->update(['status' => $request->status]);
            }
            return success('', 200, '审核成功');

        } elseif ($money->type == '提现'){
            # 提现只需要修改状态，重置冻结金额即可
            # 1. 通过提现申请
            if($request->status == 2){
                $info = Money::where('id', $request->id)->update(['status' => $request->status, 'fro_money'=>0]);
                # 1.1 添加手续费日志
                $withdraw_fee = Config::get_value('withdraw_fee');
                $original_money = $money->fro_money/(1 + $withdraw_fee);
                $bond = $original_money * $withdraw_fee;  #收取的手续费


                $fee['user_id'] = $money->user_id;
                $fee['money'] = $bond;
                $fee['money_id'] = $money->id;
                $fee['description'] = '用户提现通过，手续费用为' . $bond;
                WithdrawFee::create($fee);

            } elseif ($request->status == 3){
                # 2. 拒绝申请, 将冻结金额还给原账户
                $fro_money = $money->fro_money;
                DB::beginTransaction();
                $info1 = UserInfo::where('user_id', $money->user_id)->increment('money', $fro_money);;
                $info2 = Money::where('id', $request->id)->update(['status' => $request->status, 'fro_money'=>0]);
                if($info1 && $info2){
                    DB::commit();
                } else{
                    DB::rollBack();
                }

            }
            return success('', 200, '审核成功');
        }
    }

    public function pic_list()
    {
        $wechat     = Config::get_value('pay_wechat');

        $we_path                = 'storage/' . basename($wechat);
        $data['we_url']         = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $we_path;
        $data['we_account']     = Config::get_value('we_account');

        $ali                    = Config::get_value('pay_ali');
        $a_path                 = '/storage/' . basename($ali);
        $data['a_url']          =  'http://' . $_SERVER['HTTP_HOST'] . '/' . $a_path;
        $data['ali_account']    = Config::get_value('ali_account');


        return success($data);
    }

}
