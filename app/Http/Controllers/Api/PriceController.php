<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    //
    public function store(Request $request)
    {

        try{
            $info = Price::create($request->all());
        } catch (\PDOException $e){
            return success('', 400, '检查面值是否重复');
        }
        return success($info, 200, '添加成功');
    }

    public function index(Request $request)
    {
        $game_id        = $request->game_id;
        $gold          = $request->gold;
        $status         = $request->status;
        if($status){
            $status = [$status];
        }else{
            $status = [1, 2];
        }

        $price_id = $request->price_id;
        $title = $request->title;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);
        $pass           = $request->pass && $request->pass == 'passed' ? 1 : 2;

        $query          = Price::join('games', 'games.id', '=', 'prices.game_id')
                        ->when($game_id, function ($query, $game_id) {
                            return $query->where('games.id', $game_id);
                        })
                        ->when($gold, function($query, $gold){
                            return $query->where('prices.gold',  'like', '%' .  $gold . '%');
                        })
            ->when($price_id, function($query, $price_id){
                return $query->where('prices.id', $price_id);
            })
            ->when($title, function($query, $title){
                return $query->where('prices.title',   'like', '%' .  $title . '%');
            })
                        ->where('prices.pass', $pass)
                        ->whereIn('prices.status', $status)
                        ->select('prices.id', 'prices.gold', 'prices.title', 'prices.money', 'prices.status', 'prices.created_at',  'games.name');
        $data['total'] = $query->count();
        $data['data']   = $query
                        ->orderBy('prices.' . $sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();


        return success($data, 200);
    }

    public function delete(Request $request)
    {
        $info = Price::destroy($request->id);
        return success($info, 200, '删除成功');
    }

    public function detail(Request $request)
    {
        $data = Price::where('id', $request->id)->select('gold', 'title', 'money', 'game_id')->first();
        return success($data);
    }

    public function update(Request $request)
    {
        try{
            $info = Price::where('id', $request->id)->update($request->all());

        } catch (\PDOException $e){
            return success('', 400, '检查面值名称或标识是否重复');
        }
        return success($info, 200, '修改成功');
    }


    public function status(Request $request)
    {
        $price = Price::find($request->id);
        $price->status = Price::handleStatus($price->status);
        $info = $price->save();
        return success($info, 200, '修改成功');
    }

    public function pass(Request $request)
    {
        $pass = Price::find($request->id);
        if($pass->pass == 1){
            $pass->pass = 2;
        } else{
            $pass->pass = 1;
        }
        $info = $pass->save();
        return success($info, 200, '修改成功');

    }

    public function select(Request $request)
    {
        $data = Price::where('game_id', $request->game_id)
                ->select('id', 'gold')
                ->get();
        return success($data);
    }
}
