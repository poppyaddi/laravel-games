<?php

namespace App\Exports;

use App\Http\Controllers\Port\TokenEncController;
use App\Models\Config;
use App\Models\Son;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Concerns\FromCollection;

use Illuminate\Support\Collection;

class StockExport implements FromCollection
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
            ->select('id','price as store_price', 'identifier', 'input_user_id', 'owner_user_id', 'price_id', 'user_type', 'status', 'use_time', 'created_at', 'stores.currency', 'start_time', 'new_receipt', 'receipt');

//        $data['total'] = $query->count();
        $data      = $query
                ->orderBy($sort_field, $order)
                ->get();

        $decrypt = new TokenEncController();

        $res[0] = ['id', '游戏名称', '面值名称', '面值价格', '库存单号', '所属用户', '凭证状态', '新凭证', '旧凭证', '入库时间', '使用时间'];
//        return success($data);

        for($i=0; $i<count($data); $i++){
            $res[$i+1][0] = $data[$i]->id;
            $res[$i+1][1] = $data[$i]->price->game->name;
            $res[$i+1][2] = $data[$i]->price->gold;
            $res[$i+1][3] = $data[$i]->price->money;
            $res[$i+1][4] = (string) $data[$i]->identifier;
            $res[$i+1][5] = $data[$i]->user->name;
            $res[$i+1][6] = $data[$i]->status;
            $res[$i+1][7] = $decrypt->token_public_decrypt($data[$i]->receipt);
            $res[$i+1][8] = $decrypt->token_public_decrypt($data[$i]->new_receipt);
            $res[$i+1][9] = $data[$i]->start_time;
            $res[$i+1][10] = $data[$i]->use_time;
        }

        return $res;
    }
}
