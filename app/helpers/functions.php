<?php

function success ($data, $code=200, $message=''){
    $data = ['data'=>$data, 'code'=>$code, 'message'=>$message];
    return response()->json($data, 200);
}

function error ($data, $code=400, $message=''){
    $data = ['data'=>$data, 'code'=>$code, 'message'=>$message];
    return response()->json($data, 400);
}

function get_real_order($order){
    if($order == ''){
        $order = 'desc';
    }elseif(substr($order, 0, 1) == 'a'){
        $order = 'asc';
    } else{
        $order = 'desc';
    }
    return $order;
}

function get_tree($array, $pid = 0){

    $arr = array();
    $temp = array();
    foreach ($array as $v){

        if($v['parent_id'] == $pid){

            $temp = get_tree($array, $v['id']);
            //判断是否存在子数组

            for ($i=0; $i < count($temp); $i++) {

                if($temp){
                    $v['children'] = $temp;
                }
            }
            $arr[] = $v;
        }
    }
    return $arr;
}


