<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use App\Models\Role;
class RoleController extends Controller
{
    //
    public function index(Request $request)
    {
        if($request->get('id')){
            $data = Role::find($request->id);
        } else{
            $data = Role::all();
        }
        return success($data, 200);
    }

    public function store(RoleRequest $request)
    {
        try {
            $info = Role::create($request->all());
        } catch (PDOException $e) {
            return success('', 40000, '添加失败,查看是否重复');
        }
        return success($info, 201,'添加成功');
    }

    public function update(Request $request)
    {

        $info = Role::where(['id'=>$request->id])->update($request->all());
        return success($info, 200, '修改成功');
    }

    public function delete(Request $request)
    {
        $info = Role::destroy($request->id);
        return success($info, 200);
    }

    public function detail(Request $request)
    {
        return success($request->id);
    }

    public function test()
    {
        $data = Role::with('user')->get();
        return $data;
    }


}
