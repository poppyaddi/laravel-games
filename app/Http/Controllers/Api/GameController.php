<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    //
    public function store(Request $request)
    {

        try{
            $info = Game::create($request->all());
        }
        catch (\PDOException $e){
            return success('', 400, '检查游戏或包名是否重复');
        }
        return success($info, 200, '添加成功');
    }

    public function index(Request $request)
    {
        $name           = $request->name;
        $product        = $request->productIdentifier;
        $status         = $request->status;
        if($status){
            $status = [$status];
        } else{
            $status = [1, 2];
        }

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $query          = Game::when($name, function ($query, $name) {
                            return $query->where('name', 'like', '%' .  $name . '%');
                        })
                        ->when($product, function($query, $product){
                            return $query->where('productIdentifier',  'like', '%' .  $product . '%');
                        })
                        ->when($status, function($query, $status){
                            return $query->whereIn('status', $status);
                        });
        $data['total'] = $query->count();
        $data['data']   = $query
                        ->orderBy($sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();


        return success($data, 200);
    }

    public function delete(Request $request)
    {
        try{
            $info = Game::destroy($request->id);
            return success($info, 200, '删除成功');
        } catch (\PDOException $e){
            return success('', 200, '删除失败!!!先删除该游戏下的所有标识');
        }

    }

    public function detail(Request $request)
    {
        $data = Game::where('id', $request->id)->select('name', 'productIdentifier', 'description')->first();
        return success($data);
    }

    public function update(Request $request)
    {
        try{
            $info = Game::where('id', $request->id)->update($request->all());
        } catch (\PDOException $e){
            return success('',  400, '检查游戏是否重复');
        }
        return success($info, 200, '修改成功');
    }

    public function status(Request $request)
    {
        $game = Game::find($request->id);
        $game->status = Game::handleStatus($game->status);
        $info = $game->save();
        return success($info, 200, '修改成功');
    }

    public function select()
    {
        $data = Game::select('id', 'name')->get();
        return success($data);
    }

}
