<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\InoutLog;
use App\Models\Son;
use App\Models\UserLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    //
    public function user(Request $request)
    {
        $user_id = $request->user_id;
        $start_time = $request->start_time;
        $end_time = $request->end_time;
        $type = $request->user_type;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        # 默认显示该账户下所有子账户日志

        $user = auth('api')->user();
        # 管理员显示所有人
        $in = [];
        if($user->role_id != 1){
            $in = Son::where('user_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        $in = null;
        $with = $type ?? 'user';
        if($user->role_id != 1){
            # 非管理员显示自己的登陆日志
            if($type == 'user'){
                # 主账户日志
                $in = [$user->id];
            } elseif ($type == 'user'){
                # 显示子账户日志
                # 只显示自己主账户下的子账户日志
                $in = Son::where('user_id', $user->id)->pluck('id')->toArray();
            }
        }

        if($type == 'son'){
            $type = 2;
        } else{
            $type = 1;
        }

        $query = UserLog::when($in, function($query, $in){
                return $query->whereIn('id', $in);
            })
            ->when($user_id, function($query, $user_id){
                return $query->where('user_id', $user_id);
            })
            ->when($start_time, function ($query, $start_time){
                return $query->where('created_at', '>', $start_time);
            })
            ->when($end_time, function ($query, $end_time){
                return $query->where('created_at', '<', $end_time);
            })
            ->with([$with => function($query){
                return $query->select('id', 'name');
            }])
            ->where('type', $type)
            ->select('id', 'user_id', 'description', 'login_ip', 'created_at');

        $data['total'] = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();
        return success($data);
    }

    public function fee(Request $request)
    {
        $user_id = $request->user_id;
        $start_time = $request->start_time;
        $end_time = $request->end_time;
        $type = $request->user_type;

        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        # 默认显示该账户下所有子账户日志

        $user = auth('api')->user();
        # 管理员显示所有人
        $in = [];
        if($user->role_id != 1){
            $in = Son::where('user_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        $in = null;
        $with = $type ?? 'user';
        if($user->role_id != 1){
            # 非管理员显示自己的登陆日志
            if($type == 'user'){
                # 主账户日志
                $in = [$user->id];
            } elseif ($type == 'user'){
                # 显示子账户日志
                # 只显示自己主账户下的子账户日志
                $in = Son::where('user_id', $user->id)->pluck('id')->toArray();
            }
        }

        if($type == 'son'){
            $type = 2;
        } else{
            $type = 1;
        }

        $query = Fee::when($in, function($query, $in){
            return $query->whereIn('id', $in);
        })
            ->when($user_id, function($query, $user_id){
                return $query->where('user_id', $user_id);
            })
            ->when($start_time, function ($query, $start_time){
                return $query->where('created_at', '>', $start_time);
            })
            ->when($end_time, function ($query, $end_time){
                return $query->where('created_at', '<', $end_time);
            })
            ->with([$with => function($query){
                return $query->select('id', 'name');
            }])
            ->select('id', 'user_id', 'description', 'money', 'created_at');

        $data['total'] = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();
        return success($data);

    }
}
