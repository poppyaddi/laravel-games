<?php

namespace App\Exports;

use App\Models\Son;
use App\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class InStockExport implements FromCollection
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

    protected function handle_data($request){
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

        $query = \App\Models\Store::join('prices', 'prices.id', '=', 'stores.price_id')
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
            ->select('stores.id', 'games.name as game_name', 'prices.gold', 'prices.money', 'sons.name as son_name', DB::raw('count(concat(price_id, input_user_id)) as total, money * count(concat(price_id, input_user_id)) as total_money'));

        $header = ['id', '游戏名称', '面值名称', '面值价格', '子账户名称', '总数', '总额'];

        $data       = $query
                    ->groupBy('price_id', 'input_user_id')
                    ->orderBy('stores.' . $sort_field, $order)
                    ->get()->toArray();

        array_unshift($data, $header);


        return $data;
    }
}
