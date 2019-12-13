<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    //
    public function store(Request $request)
    {
        $info = Config::create($request->all());

        return success($info, 200, '添加成功');
    }

    public function index(Request $request)
    {
        $key          = $request->key;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $query =        Config::when($key, function($query, $key){
                            return $query->where('key', $key);
                        });

        $data['total']  = $query->count();

        $data['data']   = $query
                        ->orderBy($sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();

        return success($data);
    }

    public function detail(Request $request)
    {
        $data = Config::find($request->id);
        return success($data);
    }

    public function update(Request $request)
    {
        $info = Config::where('id', $request->id)->update($request->all());
        return success($info, 200, '修改成功');
    }

    public function pagesize()
    {
        return success(Config::get_value('pagesize'));
    }
}
