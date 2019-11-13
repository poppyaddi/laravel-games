<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MenuController extends Controller
{
    //
    public function store(Request $request)
    {
        $info = Menu::create($request->all());
        return success($info, 201, '添加成功');

    }

    public function index(Request $request)
    {
        $title          = $request->title;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $query          = Menu::when($title, function ($query, $title) {
                            return $query->where('title', $title);
                        });
        $data['total'] = $query->count();
        $data['data']   = $query->with(['parent'=> function($query){
                            return $query->select('id', 'title');
                        }])
                        ->orderBy($sort_field, $order)
                        ->offset($offset)
                        ->limit($pagesize)
                        ->get();


        return success($data, 200);
    }

    public function delete(Request $request)
    {
        $info = Menu::destroy($request->id);
        return success($info, 200);
    }

    public function detail(Request $request)
    {
        $data = Menu::find($request->id);
        return success($data, 200);
    }

    public function update(Request $request)
    {

        # 修改只有save触发修改器, 自定义处理数据
        $data = Menu::handleType($request->all());
//        return success($data);
        $info = Menu::where('id', $request->id)->update($data);
        return success($info, 200, '修改成功');
    }

    /**
     * 添加或修改菜单时下拉选框数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSelectList()
    {
        $data =  Menu::where('type', 'm')
            ->select('id', 'title')
            ->get();
        return success($data, 200);
    }


    /**
     * 角色分配菜单时获取树状列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function tree()
    {
        $data   = Menu::where('type', 'm')
                ->select('id', 'title', 'parent_id')
                ->get();
        $tree   = get_tree($data);
        return success($tree, 200);
    }

    /**
     * 加载layout时获取用户菜单列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function user_menu()
    {
        # 获取用户角色, 然后获取该角色对应菜单
        $role_id        = Role::get_role_id();
        $menu_id        = RoleMenu::where('role_id', $role_id)->first()->menu_id;
        $menu_id        = explode(',', $menu_id);
        $all_menu_id    = Menu::get_all_menus($menu_id);

        $data           = Menu::where('type', 'm')
                        ->whereIn('id', $all_menu_id)
                        ->orderBy('order')
                        ->select('id', 'icon', 'title', 'path', 'parent_id')
                        ->get();
        $data           = get_tree($data);
        return success($data);
    }
}
