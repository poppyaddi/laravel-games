<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Son;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    //
    public function index(Request $request)
    {
        $name          = $request->name;
        $device         = $request->device;
        $son_id         = $request->son_id;
        $status         = $request->status;
        $page           = $request->page ?? 1;
        $pagesize       = $request->pageSize ?? 15;
        $offset         = $pagesize * ($page - 1);
        $sort_field     = $request->sortField ?? 'id';
        $order          = get_real_order($request->sortOrder);

        $query = Device::when($device, function($query, $device){
                    return $query->where('devices.device', 'like', '%' . $device . '%');
                })->join('sons', 'sons.id', '=', 'devices.son_id')
                ->when($son_id, function ($query, $son_id){
                    return $query->where('sons.id', $son_id);
                })
                ->when($status, function($query, $status){
                    return $query->where('devices.status', $status);
                })
                ->select('devices.id', 'devices.device', 'devices.status', 'devices.created_at', 'sons.name');

        $data['total'] = $query->count();
        $data['data']   = $query
            ->orderBy($sort_field, $order)
            ->offset($offset)
            ->limit($pagesize)
            ->get();


        return success($data, 200);
    }

    public function select()
    {
        $data = Son::select('id', 'name')->get();
        return succ($data);
    }

    public function delete(Request $request)
    {
        $info = Device::destroy($request->id);
        return succ($info, 200, '删除成功');
    }

    public function status(Request $request)
    {
        $device = Device::find($request->id);
        if($device->status == '启用'){
            $device->status = 3;
        } else{
            $device->status = 2;
        }
        $info = $device->save();
        return success($info, 200, '修改成功');
    }
}
