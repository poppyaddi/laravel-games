<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Perm;
use App\Models\Role;
use App\Models\RolePerm;
use Illuminate\Http\Request;

class PermController extends Controller
{
    //
    public function store(Request $request)
    {
        $info = Perm::create($request->all());
        return success($info, 201, '添加成功');
    }

    public function index(Request $request)
    {
        $name          = $request->name;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $query =        Perm::when($name, function($query, $name){
                            return $query->where('name', $name);
                        });

        $data['total']  = $query->count();

        $data['data']   = $query->with(['parent'=>function($query){
                            return $query->select('id', 'name');
                        }])
                        ->where('id', '<>', 1)
                        ->orderBy($sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();

        return success($data);
    }

    public function delete(Request $request)
    {
        $info = Perm::destroy($request->id);
        return success($info);
    }


    public function getSelectList()
    {
        $data = Perm::where('parent_id', 1)
                ->select('id', 'name')
                ->get();
        # 如果是空, 可返回默认数据
         $data = !$data->isEmpty() ? $data : [['id'=>1, 'name'=>'无父级权限']];
        return success($data);
    }

    public function detail(Request $request)
    {
        $data = Perm::find($request->id);
        return success($data);
    }

    public function update(Request $request)
    {
        $info = Perm::where(['id'=>$request->id])->update($request->all());
        return success($info, 200, '修改成功');
    }

    public function test(Request $request)
    {
        $path = $request->path();
        $path = array_slice(explode('/', $path),2);
        $path =implode('/',$path);

        $role_id = Role::get_role_id();

        $perm_id = RolePerm::where('role_id', $role_id)->first()->perm_id;
        $perm_id = $perm_id != '' ? explode(',', $perm_id): [];
        $perms = Perm::whereIn('id', $perm_id)->pluck('uri')->toArray();
        if(in_array($path, $perms)){
            return 1;
        } else{
            return 2;
        }
        return $perms;
    }

    public function tree()
    {
        $data   = Perm::where('id', '<>', '1')
                ->select('id', 'name as title', 'parent_id')
                ->get();
        $tree   = get_tree($data, 1);
        return success($tree, 200);
    }
}
