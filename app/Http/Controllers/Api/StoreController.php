<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Port\TokenEncController;
use App\Http\Controllers\Port\TransactionController;
use App\Models\Config;
use App\Models\Son;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    //
    public function index(Request $request)
    {
        # 获取参数
        $game_id        = $request->game_id;
        $price_id       = $request->price_id;
        $type           = $request->user_type;
        $status         = $request->status;
        $identifier     = $request->identifier;
        $user_id        = $request->user_id;
        $start_time     = $request->start_time;
        $end_time       = $request->end_time;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $type = $type ? $type : Config::get_value('default_receipt_type');
        $user_type = $type == 'son' ? 2 : 1;

        # 判断是否为管理员，不是管理员则只能看自己的库存
        $user = auth('api')->user();
        $in = [];
        if($user->role_id != 1){
            if($type == 'user'){
                $in = [$user->id];
            } elseif ($type == 'son'){
                $in = Son::where('user_id', $user->id)->pluck('id')->toArray();
            }
        }

        $query = Store::with(['price'=>function($query){
                    return $query->with(['game'=>function($query){
                                return $query->select('id', 'name');
                            }])
                            ->select('id', 'game_id', 'gold', 'money');
                }])
                ->with(['input'=>function($query){
                    return $query->select('id', 'name');
                }])
                ->with([$type => function($query){
                    return $query->select('id', 'name');
                }])
                ->when($game_id, function ($query, $game_id){
                    return $query->where('game_id', $game_id);
                })
                ->when($price_id, function($query, $price_id){
                    return $query->where('price_id', $price_id);
                })
                ->when($status, function($query, $status){
                    return $query->where('status', $status);
                })
                ->when($identifier, function($query, $identifier){
                    return $query->where('identifier', $identifier);
                })
                ->when($start_time, function($query, $start_time){
                    return $query->where('created_at', '>', $start_time);
                })
                ->when($end_time, function($query, $end_time){
                    return $query->where('created_at', '<', $end_time);
                })
                ->when($user_id, function($query, $user_id){
                    return $query->where('owner_user_id', $user_id);
                })
                ->where('user_type', $user_type)
                ->when($in, function($query, $in){
                    return $query->whereIn('owner_user_id', $in);
                })
                ->select('id','price', 'identifier', 'input_user_id', 'owner_user_id', 'price_id', 'user_type', 'status', 'use_time', 'created_at', 'stores.currency')
                ;

        $data['total'] = $query->count();
        $data['data']   = $query
                        ->orderBy($sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();
        return success($data);
    }

    public function detail(Request $request)
    {
        $receipt        = Store::find($request->id)->receipt;

        $receipt        = (new TokenEncController())->token_public_decrypt($receipt);

        $apple_verify   = apple_verify($receipt);
//        return success($apple_verify);

        $data           = ['receipt'=>$receipt, 'buy_time'=>date('Y-m-d H:i:s',     substr($apple_verify->receipt->original_purchase_date_ms, 0, 10))];
        return success($data);
    }

    public function status(Request $request)
    {

        $info = Store::where('id', $request->id)->update(['status'=>$request->status]);
        return success($info, 200, '状态修改成功');
    }

    public function in_index(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $game_id        = $request->game_id;
        $son_id         = $request->user_id;
        $start_time     = $request->start_time;
        $end_time       = $request->end_time;

        # 判断权限
        $user           = auth('api')->user();
        $in             = null;
        if($user->role_id != 1){
            $in =  Son::where('user_id', $user->id)->pluck('id')->toArray();
        }

        $query = Store::join('prices', 'prices.id', '=', 'stores.price_id')
                ->join('games', 'games.id', '=', 'stores.game_id')
                ->join('sons', 'sons.id', '=', 'stores.input_user_id')
                ->when($in, function($query, $in){
                    return $query->whereIn('input_user_id', $in);
                })
                ->when($game_id, function($query, $game_id){
                    return $query->where('stores.game_id', $game_id);
                })
                ->when($son_id, function($query, $son_id){
                    return $query->where('stores.input_user_id', $son_id);
                })
                ->when($start_time, function($query, $start_time){
                    return $query->where('stores.created_at', '>', $start_time);
                })
                ->when($end_time, function($query, $end_time){
                    return $query->where('stores.created_at', '<', $end_time);
                })
                ->select('stores.id', 'games.name as game_name', 'prices.gold', 'price_id',  'prices.money', 'sons.name as son_name', DB::raw('count(concat(price_id, input_user_id)) as total'));

        $data['totalMoney'] = $query->sum('prices.money');

        $data['total']      = $query->distinct('price_id', 'input_user_id')->count();

        $data['data']       = $query
                            ->groupBy('price_id', 'input_user_id')
                            ->orderBy('stores.' . $sort_field, $order)
                            ->offset($offset)
                            ->limit($pagesize)
                            ->get();

        return success($data);

    }
}
