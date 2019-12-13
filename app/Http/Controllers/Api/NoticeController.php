<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    //
    public function store(Request $request)
    {
        $info = Notice::create($request->all());
        return success($info, 200, '添加成功');
    }

    public function index(Request $request)
    {

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $title = $request->title;

        $query          = Notice::when($title, function ($query, $title) {
                            return $query->where('title', 'like',  '%' . $title . '%');
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
        $info = Notice::destroy($request->id);
        return success($info, 200, '删除成功');
    }

    public function detail(Request $request)
    {
        $data = Notice::find($request->id);
        return success($data);
    }

    public function update(Request $request)
    {

        $info = Notice::where('id', $request->id)->update(['title'=>$request->title, 'content'=>$request->content]);
        return success($info, 200, '修改成功');
    }

    public function display()
    {
        $user = auth('api')->user();
        $notice = Notice::orderBy('id', 'desc')->first();

        $user_ids = explode(',', $notice->user_ids) ?? [];

        if(!in_array($user->id, $user_ids)){
            # 不在里面就返回数据并添加到
            array_push($user_ids, $user->id);
            $notice->user_ids = implode(',', $user_ids);
            $notice->save();
            $notice->expire_time = Config::get_value('notice_expire_time');
            return success($notice);
        }

        return success('');

    }


}
