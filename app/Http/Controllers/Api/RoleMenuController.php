<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Http\Request;

class RoleMenuController extends Controller
{
    //
    public function store(Request $request)
    {
        $pid = Menu::first()->parent_id;
        $first_level = Menu::where('parent_id', $pid)->pluck('id')->toArray();
        $second_level = array_values(array_diff($request->menu_id, $first_level));
        $second_level  = $second_level ? implode(',', $second_level) : '';
        $info = RoleMenu::updateOrCreate(
                    ['role_id'=>$request->role_id],
                    ['menu_id'=>$second_level]
                );
        return success($info);
    }

    public function index(Request $request)
    {
        $menu            = RoleMenu::where('role_id', $request->role_id)
                            ->first();
        $data['menu_id']    = $menu && $menu->menu_id != '' ? explode(',', $menu->menu_id) : [];
        return success($data);
    }


}
