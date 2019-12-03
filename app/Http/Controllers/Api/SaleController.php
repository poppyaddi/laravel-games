<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Sale;
use App\Models\SaleLog;
use App\Models\Store;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    //

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

        $query          = Sale::whereIn('status', [1, 2])
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

    public function store(Request $request)
    {

        $data   = $request->all();

        # 1. 判断当前用户是否有有效数量的可上架凭证
        $count  = $this->getCount($request);
        if($count < $request->unit){
            return error('', 400, '有效凭证数量不足');
        }

        $user   = auth('api')->user();

        $data['user_id'] = $user->id;

        # 2. 获取指定数量的凭证
        $where  = [
                        ['user_type', '=', 1],
                        ['owner_user_id','=', $user->id],
                        ['status', '=', 1],
                        ['game_id', '=', $request->game_id],
                        ['price_id', '=', $request->price_id]
                  ];
            $store_ids          = Store::where($where)
                                ->limit($request->unit)
                                ->pluck('id')
                                ->toArray();
        # 2.1 将这些凭证的状态改为上架
        $data['store_id']       = implode(',', $store_ids);

        # 3. 生成订单号
        $order_num              = $this->get_order();
        $data['order_num']      = $order_num;
        $data['default_unit']   = $request->unit;

        # 4. 保存数据库
        DB::transaction(function () use ($data, $store_ids) {
            Store::whereIn('id', $store_ids)->update(['status'=>8]);
             Sale::create($data);
        });

        return success('', 200, '添加成功');

    }

    public function remain(Request $request)
    {

        return success($this->getCount($request));
    }

    public function getCount($request)
    {
        $user   =auth('api')->user();
        $where  = [
                    ['user_type', '=', 1],
                    ['owner_user_id','=', $user->id],
                    ['status', '=', 1],
                    ['game_id', '=', $request->game_id],
                    ['price_id', '=', $request->price_id]
                ];
        return Store::where($where)->count();
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

    public function down(Request $request)
    {
        # 判断权限，只有自己或者管理员才能下架
        $id     = $request->id;
        $user   = auth('api')->user();
        $sale   = Sale::find($id);

        if($user->id != $sale->user_id && $user->role_id != 1){
            return error('', 400, '无权限');
        }

        DB::transaction(function () use ($id, $sale) {
            Sale::where('id', $id)->update(['status'=>5]);
            Store::whereIn('id', explode(',', $sale->store_id))->update(['status'=>1]);
        });
        return success('', 200, '下架成功');
    }

    public function buy(Request $request)
    {
        $unit       = $request->unit;  # 购买数量
        $pay_pass   = $request->password;  # 支付密码
        $id         = $request->id;
        $user       = auth('api')->user();

        # 0. 验证交易密码
        $user_info  = UserInfo::where('user_id', $user->id)->first();
        if (sha1($pay_pass) != $user_info->pay_pass){
            return error('', 400, '交易密码错误');
        }

        $sale       = Sale::find($id);
        $store_ids  = explode(',', $sale->store_id);

        # 1. 判断数量是否足够
        if($unit > count($store_ids)){
            return error('', 400, '订单数量不足');
        }

        # 2. 将购买的凭证转给购买者，并修改凭证状态
        $buyed      = array_slice($store_ids, 0, $unit);


        # 3. 修改订单状态
        $left       = array_values(array_diff($store_ids, $buyed));
        $left_count = count($left);

        $status = count($left) == 0 ? 3 : 2;

        $left       = implode(',', $left);

        DB::beginTransaction();

        # 1. 修改凭证所有人
        $info1      = Store::whereIn('id', $buyed)
                    ->update(['status'=>1, 'owner_user_id' => $user->id]);
        # 2. 修改订单信息
        $info2      = Sale::where('id', $id)
                    ->update(['status'=>$status, 'store_id'=>$left, 'unit'=>$left_count]);
        # 3. 扣钱
        $tans_fee   = Config::get_value('trans_fee');
        $info3      = UserInfo::where('user_id', $user->id)
                    ->decrement('money', $unit * $sale->unit_price);

        # 4. 加钱
        $info4      = UserInfo::where('user_id', $sale->user_id)
                    ->increment('money', $unit * $sale->unit_price * (1 - $tans_fee));

        # 5. 添加购买日志
        $log['user_id']     = $user->id;
        $log['sale_id']     = $id;
        $log['store_id']    = implode(',', $buyed);
        $log['description'] = '购买订单';
        $log['price']       = $sale->unit_price;
        $info5              = SaleLog::create($log);

        if($info1 && $info2 && $info3 && $info4){
            DB::commit();
            return success('', 200, '购买成功');
        } else{
            DB::rollBack();
            return error('', 400, '购买失败');
        }
    }

    public function update(Request $request)
    {
        $id         = $request->id;

        $user       = auth('api')->user();

        $sale   = Sale::find($id);

        # 只有自己或管理员才能修改
        if($user->id != $sale->user_id && $user->role_id != 1){
            return error('', 400, '无权限');
        }

        # 0. 验证交易密码
        $user_info  = UserInfo::where('user_id', $user->id)->first();
        if (sha1($request->password) != $user_info->pay_pass){
            return error('', 400, '交易密码错误');
        }
        # 1. 修改订单
        $info       = Sale::where('id', $request->id)
                    ->update(['unit_price'=>$request->unit_price]);

        return success($info, 200, '修改成功');
    }

    public function me(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $query          = Sale::whereIn('status', [1, 2])
                        ->where('user_id', auth('api')->user()->id)
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
}
