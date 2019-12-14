<?php

namespace App\Http\Controllers\Api;

use App\Exports\InStockExport;
use App\Exports\StatisticExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Port\TokenEncController;
use App\Http\Controllers\Port\TransactionController;
use App\Models\Config;
use App\Models\InoutLog;
use App\Models\Son;
use App\Models\Store;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;

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
        $id             = $request->id;

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
                ->when($id, function($query, $id){
                    return $query->where('id', $id);
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

        $data           = ['receipt'=>$receipt, 'buy_time'=>date('Y-m-d H:i:s',     substr($apple_verify->receipt->original_purchase_date_ms, 0, 10)), 'identifier'=>$apple_verify->receipt->transaction_id];


        return success($data);
    }

    public function status(Request $request)
    {

        $info = Store::where('id', $request->id)->update(['status'=>$request->status]);
        return success($info, 200, '状态修改成功');
    }

    /**
     * 入库列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function in_index(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $game_id        = $request->game_id;
        $price_id       = $request->price_id;
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
                ->when($price_id, function($query, $price_id){
                    return $query->where('stores.price_id', $price_id);
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

    /**
     * 出库列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function out_index(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $game_id        = $request->game_id;
        $price_id       = $request->price_id;
        $son_id         = $request->user_id;
        $start_time     = $request->start_time;
        $end_time       = $request->end_time;

        # 判断权限
        $user           = auth('api')->user();
        $in             = null;
        if($user->role_id != 1){
            $in =  Son::where('user_id', $user->id)->pluck('id')->toArray();
        }

        $query = InoutLog::where('inout_logs.type', 2)
                ->join('stores', 'stores.id', '=', 'inout_logs.store_id')
                ->join('prices', 'prices.id', '=', 'stores.price_id')
                ->join('games', 'games.id', '=', 'stores.game_id')
                ->join('sons', 'sons.id', '=', 'stores.owner_user_id')
                ->when($in, function($query, $in){
                    return $query->whereIn('owner_user_id', $in);
                })
                ->where('user_type', 2)
                ->whereIn('stores.status', [2, 4, 6])
                ->when($game_id, function($query, $game_id){
                    return $query->where('stores.game_id', $game_id);
                })
                ->when($price_id, function($query, $price_id){
                    return $query->where('stores.price_id', $price_id);
                })
                ->when($son_id, function($query, $son_id){
                    return $query->where('stores.owner_user_id', $son_id);
                })
                ->when($start_time, function($query, $start_time){
                    return $query->where('stores.created_at', '>', $start_time);
                })
                ->when($end_time, function($query, $end_time){
                    return $query->where('stores.created_at', '<', $end_time);
                })
                ->select('inout_logs.id', 'games.name as game_name', 'prices.gold', 'price_id',  'prices.money', 'sons.name as son_name', 'stores.status', DB::raw('count(concat(price_id, owner_user_id)) as total'));

        $data['totalMoney'] = $query->sum('prices.money');

        $data['total']      = $query->distinct('price_id', 'owner_user_id')->count();

        $data['data']       = $query
                            ->groupBy('price_id', 'owner_user_id')
                            ->orderBy('stores.' . $sort_field, $order)
                            ->offset($offset)
                            ->limit($pagesize)
                            ->get();

        return success($data);

    }

    /**
     * 数据统计列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistic(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $type           = $request->user_type;
        $user_id        = $request->user_id;

        $game_id        = $request->game_id;
        $price_id        = $request->price_id;
        $start_time     = $request->start_time;
        $end_time       = $request->end_time;

        $type = $type ? $type : Config::get_value('default_receipt_type');

        switch ($type){
            case 'son':
                $user_type = 2;
                break;
            case 'user':
                $user_type = 1;
                break;
            default:
                $user_type = null;
        }

        # 判断权限
        $user           = auth('api')->user();

        $in = [];
        if($user->role_id != 1){
            if($type == 'user'){
                $in = [$user->id];
            } elseif ($type == 'son'){
                $in = Son::where('user_id', $user->id)->pluck('id')->toArray();
            }
        }

        $cond = null;
        if($user->role_id != 1){
            $cond['son']    =  Son::where('user_id', $user->id)->pluck('id')->toArray();
            $cond['user']   = auth('api')->user()->id;
        }

        $query = Store::join('prices', 'prices.id', '=', 'stores.price_id')
                        ->join('games', 'games.id', '=', 'stores.game_id')
                        # 查询子账户或者主账户所有的凭证
                        ->when($cond, function($query) use ($cond){
                            return $query->where(function($query) use ($cond){
                                return $query->whereIn('owner_user_id', $cond['son'])->where('user_type', 2);
                            })->orWhere(function($query) use ($cond){
                                return $query->where('owner_user_id', $cond['user'])->where('user_type', 1);
                            });
                        })
                        ->when($game_id, function($query, $game_id){
                            return $query->where('stores.game_id', $game_id);
                        })
                        ->when($price_id, function($query, $price_id){
                            return $query->where('stores.price_id', $price_id);
                        })
                        ->when($start_time, function($query, $start_time){
                            return $query->where('stores.created_at', '>', $start_time);
                        })
                        ->when($end_time, function($query, $end_time){
                            return $query->where('stores.created_at', '<', $end_time);
                        })
                        ->when($user_type, function($query, $user_type){
                            $query->where('user_type', $user_type);
                        })
                        ->when($user_id, function($query, $user_id){
                            return $query->where('owner_user_id', $user_id);
                        })
                        ->when($in, function($query, $in){
                            return $query->whereIn('owner_user_id', $in);
                        })
                        ->select('stores.id', 'games.name as game_name', 'prices.gold', 'price_id',  'prices.money', DB::raw(
                                        'sum(if(zh_stores.status=1, 1, 0)) as s1,
                                                sum(if(zh_stores.status=2, 1, 0)) as s2,
                                                sum(if(zh_stores.status=4, 1, 0)) as s4,
                                                sum(if(zh_stores.status=5, 1, 0)) as s5,
                                                sum(if(zh_stores.status=6, 1, 0)) as s6,
                                                sum(if(zh_stores.status=7, 1, 0)) as s7,
                                                sum(if(zh_stores.status=8, 1, 0)) as s8,
                                                count(concat(price_id, owner_user_id)) as total'));


        $data['totalMoney'] = $query->sum('prices.money');

        $data['total']      = $query->distinct('stores.price_id', 'stores.owner_user_id')->count();

        $data['data']       = $query
                            ->groupBy('price_id', 'owner_user_id')
                            ->orderBy('stores.' . $sort_field, $order)
                            ->offset($offset)
                            ->limit($pagesize)
                            ->get();

        return success($data);

    }

    /**
     * 库存分配列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dist_index(Request $request)
    {
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $game_id        = $request->game_id;
        $price_id       = $request->price_id;
        $start_time     = $request->start_time;
        $end_time       = $request->end_time;

        # 判断权限
        $user           = auth('api')->user();
        $in             = null;
        if($user->role_id != 1){
            $in =  $user->id;
        }


        $query = Store::join('prices', 'prices.id', '=', 'stores.price_id')
                ->join('games', 'games.id', '=', 'stores.game_id')
                ->where('user_type', 1)  # 父账户凭证
                ->whereIn('stores.status', [1, 5])
                ->when($in, function($query, $in){
                    return $query->where('owner_user_id', $in);
                })
                ->when($game_id, function($query, $game_id){
                    return $query->where('stores.game_id', $game_id);
                })
                ->when($price_id, function($query, $price_id){
                    return $query->where('stores.price_id', $price_id);
                })
                ->when($start_time, function($query, $start_time){
                    return $query->where('stores.created_at', '>', $start_time);
                })
                ->when($end_time, function($query, $end_time){
                    return $query->where('stores.created_at', '<', $end_time);
                })
                ->select('stores.id', 'games.name as game_name', 'prices.gold', 'price_id',  'prices.money', DB::raw('count(concat(price_id)) as total'));


        $data['totalMoney'] = $query->sum('prices.money');

        $data['total']      = $query->distinct('stores.price_id')->count();

        $data['data']       = $query
                            ->groupBy('price_id')
                            ->orderBy('stores.' . $sort_field, $order)
                            ->offset($offset)
                            ->limit($pagesize)
                            ->get();

        return success($data);

    }

    /**
     * 分配并转移库存(主->子)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dist(Request $request)
    {

        $price_id   = $request->price_id;
        $dis_num    = $request->dis_num;
        $son_id     = $request->son_id;
        $user       = auth('api')->user();

        # 查看$son_id是不是该账户的子账户
        $son_info = Son::find($son_id);
        if($son_info['user_id'] != $user->id){
            return error('', 400, '无权限');
        }



        # 1. 查询该账户下凭证
        $map        = [
                        ['user_type', '=', 1],
                        ['owner_user_id', '=', $user->id],
                        ['price_id', '=', $price_id],
                    ];
        $in         = [1, 5];
        $store_ids  = Store::where($map)->whereIn('status', $in)->pluck('id')->toArray();
        $dis_ids    = array_slice($store_ids, 0, $dis_num);

        # 2. 将指定凭证分配给子账户
        $info = Store::whereIn('id', $dis_ids)->update(['owner_user_id'=>$son_id, 'user_type'=>2]);
        return success($info, 200, '分配成功');
    }

    /**
     * 子账户库存列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function son_statistic(Request $request)
    {
        $son_id         = $request->son_id;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        # 判断权限
        $son_info       = Son::find($son_id);
        $user           = auth('api')->user();
        if($son_info['user_id'] != $user->id && $user->role_id != 1){
            return error('', 400, '无权限');
        }

        $map            = [
                            ['user_type', '=', 2],
                            ['owner_user_id', '=', $son_id]
                        ];


        $query = Store::join('prices', 'prices.id', '=', 'stores.price_id')
                ->join('games', 'games.id', '=', 'stores.game_id')
                ->where($map)
                ->select('stores.id', 'games.name as game_name', 'prices.gold', 'price_id',  'prices.money', DB::raw(
                'sum(if(zh_stores.status=1, 1, 0)) as s1,
                                    sum(if(zh_stores.status=5, 1, 0)) as s5,
                                    sum(if(zh_stores.status=5 or zh_stores.status=1, 1, 0)) as total'));


        $data['totalMoney'] = $query->sum('prices.money');

        $data['total']      = $query->distinct('stores.price_id')->count();

        $data['data']       = $query
                            ->groupBy('price_id')
                            ->orderBy('stores.' . $sort_field, $order)
                            ->offset($offset)
                            ->limit($pagesize)
                            ->get();

        return success($data);
    }

    public function son_to_user(Request $request)
    {
        $son_id     = $request->son_id;
//        $pay_pass   = $request->pay_pass;
        $user = auth('api')->user();

        # 查看$son_id是不是该账户的子账户
        $son_info = Son::find($son_id);
        if($son_info['user_id'] != $user->id){
            return error('', 400, '无权限');
        }

        # 查看支付密码是否正确
//        $user_info = UserInfo::where('user_id', $user->id)->first();
//
//        if(sha1($pay_pass) != $user_info->pay_pass){
//            return error('', 400, '支付密码错误');
//        }

        $map = [
            ['user_type', '=', '2'],
            ['status', '=', '1'],
            ['owner_user_id', '=', $son_id]
        ];
        $info = Store::where($map)->update(['user_type'=>1, 'owner_user_id'=>$user->id]);
        return success($info, 200, '转移成功');
    }

    /**
     * 管理员才有该权限, 一键转移账户内所有凭证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function migration(Request $request)
    {
        $user_afford    = $request->user_afford;
        $user_receiver  = $request->user_receiver;

        # 判断权限
//        if(auth('api')->user()->role_id != 1){
//            return error('', 400, '无权限');
//        }

        # 找出凭证提供者主账户所有凭证
        $map            = [
                            ['user_type', '=', 1],
                            ['owner_user_id', '=', $user_afford]
                        ];
        $user_store_ids = Store::where($map)->pluck('id')->toArray();

        # 该账户名下所有子账户的凭证
        # 1. 获取所有子账户
        $son_ids        = Son::where('user_id', $user_afford)
                        ->pluck('id')
                        ->toArray();
        # 2. 获取所有子账户的凭证
        $son_store_ids  = Store::where('user_type', 2)
                        ->whereIn('owner_user_id', $son_ids)
                        ->pluck('id')
                        ->toArray();

        $store_ids      = array_merge($user_store_ids, $son_store_ids);

        # 将所有凭证都转移给user_receiver
        $info           = Store::whereIn('id', $store_ids)
                            ->update([
                                'user_type'=>1,
                                'owner_user_id'=>$user_receiver
                        ]);

        return success($info, 200, '转移成功');


    }

    /**
     * 凭证批量删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $info = Store::whereIn('id', $request->id)->delete();
        return success($info, 200, '删除成功');
    }

    public function distribution(Request $request)
    {
        $update = [
                    'user_type'=>$request->user_type == 'user' ? '1' : '2', 'owner_user_id'=>$request->user_id
                ];

//        return success($update);
        # 上架凭证禁止分配
        $info = Store::whereIn('id', $request->id)->where('status', '<>', 8)->update($update);
        return success($info, 200, '分配成功');
    }

    public function migration_dist(Request $request)
    {
        $provider_user_id = $request->provider_user_id;
        $provider_user_type = $request->provider_user_type;
        $game_id = $request->game_id;
        $price_id = $request->price_id;
        $receiver_user_type = $request->receiver_user_type;
        $receiver_user_id = $request->receiver_user_id;
        $dis_num = $request->dis_num;

        # 1. 再次判断数量是否足够
        # 判断账户类型 user 1 son 2
        $provider_type = $provider_user_type == 'son' ? 2 : 1;
        $receiver_type = $receiver_user_type == 'son' ? 2 : 1;
        $provider_map = [
            ['user_type', '=', $provider_type],
            ['owner_user_id', '=', $provider_user_id],
            ['status', '=', 1]
        ];
        $count = Store::where($provider_map)
            ->when($game_id, function($query,$game_id){
                return $query->where('game_id', $game_id);
            })
            ->when($price_id, function ($query, $price_id){
                return $query->where('price_id', $price_id);
            })
            ->count();

        if($count < $dis_num){
            return error('', 400, '剩余凭证不足');
        }

        # 取出同等数量的凭证
        $ids = Store::where($provider_map)
            ->when($game_id, function($query,$game_id){
                return $query->where('game_id', $game_id);
            })
            ->when($price_id, function ($query, $price_id){
                return $query->where('price_id', $price_id);
            })
            ->pluck('id')
            ->toArray();
        $dis_ids = array_slice($ids, 0, $dis_num);

        # 更新凭证所有人
        $info = Store::where($provider_map)
            ->when($game_id, function($query,$game_id){
                return $query->where('game_id', $game_id);
            })
            ->when($price_id, function ($query, $price_id){
                return $query->where('price_id', $price_id);
            })
            ->whereIn('id', $dis_ids)
            ->update(['owner_user_id'=>$receiver_user_id, 'user_type'=>$receiver_type]);

        return success($info, 200, '分配成功');



    }

    public function get_count(Request $request)
    {
        $provider_user_id = $request->provider_user_id;
        $provider_user_type = $request->provider_user_type;
        $game_id = $request->game_id;
        $price_id = $request->price_id;

        # 没有选择用户，则可分配数量为0
        if(!$provider_user_id){
            return success(0);
        }

        # 判断账户类型 user 1 son 2
        $type = $provider_user_type == 'son' ? 2 : 1;

        # 获取该账户下凭证数量
        $map = [
            ['user_type', '=', $type],
            ['owner_user_id', '=', $provider_user_id],
            ['status', '=', 1]
        ];
        $count = Store::where($map)
            ->when($game_id, function($query,$game_id){
                return $query->where('game_id', $game_id);
            })
            ->when($price_id, function ($query, $price_id){
                return $query->where('price_id', $price_id);
            })
            ->count();
        return success($count);
    }

    public function store_log(Request $request)
    {
        $type = $request->type;
        $user_id = $request->user_id;
        $start_time = $request->start_time;
        $end_time = $request->end_time;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        # 默认显示该账户下所有子账户日志

        $user = auth('api')->user();
        # 管理员显示所有人
        $in = [];
        if($user->role_id != 1){
            $in = Son::where('user_id', $user->id)
                ->pluck('id')
                ->toArray();
        }
        $query = InoutLog
                ::when($in, function($query, $in){
                    return $query->whereIn('user_id', $in);
                })
                ->when($type, function($query, $type){
                    return $query->where('type', $type);
                })
                ->when($user_id, function($query, $user_id){
                    return $query->where('user_id', $user_id);
                })
                ->when($start_time, function($query, $start_time){
                    return $query->whereDate('created_at', '>', $start_time);
                })
                ->when($end_time, function($query, $end_time){
                    return $query->whereDate('created_at', '<', $end_time);
                })
                ->with(['son'=>function($query){
                    return $query->select('id', 'name');
                }])
                ->with(['store'=>function($query){
                    return $query->with(['price'=>function($query){
                        return $query->with(['game'=>function($query){
                            return $query->select('id', 'name');
                        }])
                            ->select('id', 'gold', 'game_id');
                    }])
                        ->select('id', 'price_id');
                }])
                ->select('id', 'user_id', 'description', 'created_at', 'type', 'store_id');

        $data['total'] = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();
        return success($data);
    }

    public function export_stock(Request $request)
    {
        ini_set('memory_limit','5000M');
        set_time_limit(0);

        return Excel::download(new StockExport($request), 'users.xlsx');
    }

    public function in_export(Request $request)
    {
        ini_set('memory_limit','5000M');
        set_time_limit(0);

        return Excel::download(new InStockExport($request), 'users.xlsx');
    }

    public function statistic_export(Request $request)
    {
        ini_set('memory_limit','5000M');
        set_time_limit(0);

        return Excel::download(new StatisticExport($request), 'users.xlsx');
    }


}
