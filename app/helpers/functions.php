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

function succ($param) { return returnData(['code'=> 200, 'message'=> '请求成功!'], $param); }

function err($param) { return returnData(['code'=> 400, 'message'=> '请求失败'], $param); }

/*
 * 返回 json 数据
 * 2018/06/07
 */
function returnData($data, $param)
{
    // 对象转数组
    if (is_object($param))
    {
        $param = json_decode(json_encode($param), true);
    }

    // 合并数据
    if (is_array($param))
    {
        // 登陆状态
        if (isset($param['auth']))
        {
            $data['auth'] = $param['auth'];
            unset($param['auth']);
        }
        else {
            $data['auth'] = true;
        }

        // 提示信息
        if (!empty($param['message']))
        {
            $data['message'] = $param['message'];
            unset($param['message']);
        }

        // 检查数据是否拥有下一页
        if (isset($param['page']))
        {
            $current_page = $param['page']['current_page']; // 当前第几页
            $per_page = $param['page']['per_page']; // 每页显示几条
            $total = $param['page']['total']; // 总记录数

            if ($current_page * $per_page < $total)
            {
                $param['page']['hasmore'] = true;
            }
            else {
                $param['page']['hasmore'] = false;
            }
        }

        $data['data'] = $param;
    }
    else {
        $data['message'] = $param;
    }


    return $data;
}

function curl_request($url,$ret='',$file=''){
    if (!empty($file)) {
        $ret['media'] = new CURLFile($file);
    }
    // 初始化
    $ch = curl_init();
    // 相关设置
    # 设置请求的URL地址
    curl_setopt($ch,CURLOPT_URL,$url);
    # 请求头关闭
    curl_setopt($ch,CURLOPT_HEADER,0);
    # 请求的得到的结果不直接输出，而是以字符串结果返回  必写
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    # 设置请求的超时时间 单位秒
    curl_setopt($ch,CURLOPT_TIMEOUT,30);
    # 设置浏览器型号
    curl_setopt($ch,CURLOPT_USERAGENT,'MSIE001');

    # 证书不检查
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

    # 设置为post请求
    if($ret){ # 如果 $ret不为假则是post提交
        # 开启post请求
        curl_setopt($ch,CURLOPT_POST,1);
        # post请求的数据
        curl_setopt($ch,CURLOPT_POSTFIELDS,$ret);
    }
    // 发起请求
    $data = curl_exec($ch);
    // 有没有发生异常
    if(curl_errno($ch) > 0){
        // 把错误发送给客户端
        echo curl_error($ch);
        $data = '';
    }
    // 关闭请求
    curl_close($ch);
    return $data;
}

function apple_verify($receipt){
    $url = 'https://buy.itunes.apple.com/verifyReceipt';

    $post = [
        'receipt-data' => $receipt,
    ];

    $post = json_encode($post);
    $buy = curl_request($url, $post);

    return json_decode($buy);
}


