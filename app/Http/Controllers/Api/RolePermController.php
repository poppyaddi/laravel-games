<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Perm;
use App\Models\RolePerm;
use Illuminate\Http\Request;

class RolePermController extends Controller
{
    //
    public function index(Request $request)
    {
        $perm = RolePerm::where('role_id', $request->role_id)
                ->first();
        $data['perm_id'] = $perm && $perm->perm_id != '' ? explode(',', $perm->perm_id) : [];
        return success($data);
    }

    public function store(Request $request)
    {
        $pid = Perm::first()->parent_id;
        $first_level = Perm::where('parent_id', $pid)->pluck('id')->toArray();
        $second_level = array_values(array_diff($request->perm_id, $first_level));
        $second_level  = $second_level ? implode(',', $second_level) : '';

        $info = RolePerm::updateOrCreate(['role_id'=>$request->role_id], ['perm_id'=>$second_level]);
        return success($info);
    }
}
