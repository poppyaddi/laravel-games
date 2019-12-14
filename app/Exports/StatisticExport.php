<?php

namespace App\Exports;

use App\Models\Config;
use App\Models\Son;
use App\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class StatisticExport implements FromCollection
{
    protected $request;

    function __construct($request){
        $this->request = $request;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection($this->handle_data($this->request));
    }

    public function handle_data($request)
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

        $query = \App\Models\Store::join('prices', 'prices.id', '=', 'stores.price_id')
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
                    ->select('games.name as game_name', 'prices.gold',  'prices.money', DB::raw(
                    'sum(if(zh_stores.status=1, 1, 0)) as s1,
                                            sum(if(zh_stores.status=2, 1, 0)) as s2,
                                            sum(if(zh_stores.status=4, 1, 0)) as s4,
                                            sum(if(zh_stores.status=5, 1, 0)) as s5,
                                            sum(if(zh_stores.status=6, 1, 0)) as s6,
                                            sum(if(zh_stores.status=7, 1, 0)) as s7,
                                            sum(if(zh_stores.status=8, 1, 0)) as s8,
                                            count(concat(price_id, owner_user_id)) as total'));


        $header = ['游戏名称', '面值名称', '面值价格', '正常有效', '已使用', '使用失败', '后台恢复', '手机端已获取', '凭证上架', '禁止使用', '总数'];

        $data       = $query
                    ->groupBy('price_id', 'owner_user_id')
                    ->orderBy('stores.' . $sort_field, $order)
//                    ->offset($offset)
//                    ->limit($pagesize)
                    ->get()->toArray();

        array_unshift($data, $header);

        return $data;
    }
}
