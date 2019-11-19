<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Game;
use App\Models\InoutLog;
use App\Models\Price;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Port\Rsa1024Controller;

class TransactionController extends Rsa1024Controller
{
    /**
     * 根据游戏包名获取可用凭证列表
     * @return string
     */
    public function table()
    {
        $productIdentifier = $this->param('productIdentifier'); // 游戏包名

        if (empty($productIdentifier))
        {
            return $this->RSA_private_encrypt(err('productIdentifier length is 0'));
        }

        // 获取游戏
        $map = [
            'status'=> 1, # 过滤禁用的面值
            'productIdentifier'=> $productIdentifier,
        ];

        $game = Game::where($map)->first();

        if (!$game)
        {
            return $this->RSA_private_encrypt(err('不支持这个游戏入库'));
        }

        // 获取支持面值
        $map = [
            'status'=> 1,
            'game_id'=> $game['id'],
        ];

        $sort = 'money';

//        $price = Price::where($map)->orderBy($sort)->select();

        $data= Price::where($map)->withCount(['store' => function($query){
                    $map = [
                        ['owner_user_id', '=', auth('port')->user()->id],
                        ['user_type', '=', 2]
                    ];
                    $query->whereIn('status', [1, 5])->where($map);
                }])->get();

//        $data = [];
//
//        foreach ($price as $key=> $value)
//        {
//            $map = [
//                'status'=> ['in', [1,5]],
//                'is_goods'=> 0,
//                'price_id'=> $value['id'],
//                'user_id'=> $this->user_id,
//            ];
//
//            $num = $this->store_model->where($map)->count();
//
//            $item = [];
//            $item['id'] = $value['id'];
//            $item['title'] = $value['title'];
//            $item['money'] = number_format($value['money']) . '元';
//            $item['gold'] = $value['gold'];
//            $item['num'] = $num;
//
//            $data[] = $item;
//        }

        return $this->RSA_private_encrypt(succ($data));
    }

    /**
     * 凭证入库
     * @return string
     */
    public function desmise_input()
    {
        # 判断操作账户类型(中间件中判断账户是否过期)

        if(auth('port')->user()->type == '出库'){
            return $this->RSA_private_encrypt(err('没有入库权限'));
        }

        # 处理参数
        $transactionIdentifier = $this->param('transactionIdentifier'); // 订单号
        $transactionDate = $this->param('transactionDate'); // 生成日期
        $transactionReceipt = $this->param('transactionReceipt'); // 凭证
        $newTransactionReceipt = $this->param('newTransactionReceipt'); // 新凭证

        $localizedPrice = $this->param('localizedPrice'); // 面值
        $localizedTitle = $this->param('localizedTitle'); // 标题
        $localizedDescription = $this->param('localizedDescription'); // 描述

        $productIdentifier = $this->param('productIdentifier'); // 包名

         $currency =  $this->param('currency');

        # 验证币种
        if(in_array($currency, $this->currency())){
            return $this->RSA_private_encrypt(err('币种不符合要求'));
        }

        $encrypt = new TokenEncController();


        if (empty($transactionIdentifier))
        {
            return $this->RSA_private_encrypt(err('transactionIdentifier length is 0'));
        }

        if (empty($transactionDate))
        {
            return $this->RSA_private_encrypt(err('transactionDate length is 0'));
        }

        if (empty($transactionReceipt))
        {
            return $this->RSA_private_encrypt(err('transactionReceipt length is 0'));
        }
        else
        {
            $receipt = $transactionReceipt;  # 苹果内购验证备用
            $transactionReceipt = $encrypt->token_private_encrypt($transactionReceipt);
        }

        if (empty($newTransactionReceipt))
        {
            return $this->RSA_private_encrypt(err('newTransactionReceipt length is 0'));
        }
        else
        {
            $newTransactionReceipt = $encrypt->token_private_encrypt($newTransactionReceipt);
        }

        if (empty($localizedPrice))
        {
            return $this->RSA_private_encrypt(err('price length is 0'));
        }

        if (empty($localizedTitle))
        {
            return $this->RSA_private_encrypt(err('localizedTitle length is 0'));
        }

        if (empty($localizedDescription))
        {
            return $this->RSA_private_encrypt(err('localizedDescription length is 0'));
        }

        if (empty($productIdentifier))
        {
            return $this->RSA_private_encrypt(err('productIdentifier length is 0'));
        }

        # 验证游戏类型是否支持
        $map = [
            ['productIdentifier', '=', $productIdentifier],
        ];

        $game = Game::where($map)->first();
        if (!$game)
        {
            return $this->RSA_private_encrypt(err('不支持该类型游戏'));
        }

        # 验证游戏面值是否支持
        $map = [
            ['title', '=', $localizedTitle]
        ];

        $price = Price::where($map)->first();

        if (!$price)
        {
            return $this->RSA_private_encrypt(err('不支持该面值'));
        }

        $info = Store::where('identifier', $transactionIdentifier)
                ->orWhere('receipt', $transactionReceipt)
                ->first();
        if ($info)
        {
            return $this->RSA_private_encrypt(err('凭证重复'));
        }

        # $localizedTitle 识别单个面值标识

        # return $this->RSA_private_encrypt(error($localizedTitle));

        # 获取不需验证的面值
        $passed_price_title = Price::where('pass', 1)->pluck('title')->toArray();
        # 不是跳过的凭证则需要进行苹果内购验证
        if(!in_array($localizedTitle, $passed_price_title)){
            $res = $this->apple_verify($receipt);

            if($res->status != 0 || $res->status == 21002){
                return $this->RSA_private_encrypt(err('0001'));  # 凭证验证失败
                die;
            }

            $str = substr($receipt, 0, 3);
            if($str == 'ewo'){
                $buyTime = $res->receipt->purchase_date_ms;
                # return $buyTime;  # 1569118165963  精确到毫秒
            } elseif($str == 'MII'){
                $buyTime = $res->receipt->in_app[0]->purchase_date_ms;
                # return $buyTime;  # 1569118165000
            } else{
                return $this->RSA_private_encrypt(err('0002')); # 未知错误
            }

            $buyTime = substr($buyTime, 0, 10);  // 真实购买时间
            $offset =  time() - $buyTime;

            $expire_time = Config::get_value('receipt_expire_time');
            if($offset > $expire_time){
//            $t = date('Y-m-d H:i:s', $buyTime);
//            $message = "购买时间过期：" . $t;
                return $this->RSA_private_encrypt(err('0003')); # 凭证过期
            }
        }

        // 保存凭证到库存
        $data = [
            'price'         => $localizedPrice != '---null---' ? $localizedPrice : $price['money'],
            'description'   => $localizedDescription != '---null---' ? $localizedDescription : $price['gold'],
            'game_id'       => $game['id'],
            'price_id'      => $price['id'],
            'status'        => 1,
            'start_time'    => $transactionDate,
            'identifier'    => $transactionIdentifier,
            'receipt'       => $transactionReceipt,
            'input_user_id' => $this->parent()->id,  # 入库账号的父账号id
            'owner_user_id' => auth('port')->user()->id,
            'currency'      => $currency,

        ];

        if (!empty($newTransactionReceipt))
        {
            $data['new_receipt'] = $newTransactionReceipt;
        }

        $store_id = Store::insertGetId($data);

        if ($store_id)
        {
            // 记录凭证
            $data = [
                'description'=> '手机用户入库',
                'user_id'=> auth('port')->user()->id,
                'store_id'=> $store_id,
            ];

            # 插入日志
            InoutLog::create($data);

            return $this->RSA_private_encrypt(succ('入库成功'));
        }
        else {
            return $this->RSA_private_encrypt(err('入库失败, 请稍后再试'));
        }
    }

    /**
     * 凭证出库
     * @return string
     */
    public function vendre_info_one()
    {

        # 验证账号出库权限
        if(auth('port')->user()->type == '入库'){
            return $this->RSA_private_encrypt(err('没有出库权限'));
        }

        $title = $this->param('title'); // 面值名

        if (empty($title))
        {
            return $this->RSA_private_encrypt(error('title length is 0'));
        }

        // 获取支持面值
//        $map = [
//            'status'=> 1,
//            'title'=> $title,
//        ];

        $map = [
            ['status', '=', 1],
            ['title', '=', $title]
        ];

        $price = Price::where($map)->first();

        if (!$price)
        {
            return $this->RSA_private_encrypt(error('面值未开放'));
        }

        // 获取凭证
//        $map = [
//            'is_goods'=> 0,
//            'price_id'=> $price['id'],
//            'user_id'=> auth('port')->user()->id,
//        ];
//
        $map = [
            ['price_id', '=', $price['id']],
            ['user_id', '=', auth('port')->user()->id],
        ];

        // 是否跳过使用过的凭证
        $in = $this->parent()->userinfo->pass_store == 1 ? [1, 5] : [1, 5, 6];


//        if ($userInfo['pass_store'] == 1)
//        {
//            $map['status'] = ['in', [1,5]];
//        }
//        else {
//            $map['status'] = ['in', [1, 5, 6]];
//        }

        $store = Store::whereIn('status', $in)->where($map)->orderBy('id', 'asc')->first();

        if (!$store)
        {
            return $this->RSA_private_encrypt(err('凭证不存在'));
        }

//        $store = $store[0];

        //扣除手续费
        # 如果已经出库一次，则不在扣除手续费
        // 查询凭证的价格
//        $money = db('games_price')->where(['id'=>$store['price_id']])->find()['money'];
//        $percent = db('config')->where(['key'=>'info_one'])->find()['value'];
//        $fee = $money * $percent;
//
//        $son_id = $this->user_id;
//        $pid = db('user')->where(['id'=>$son_id])->find()['pid'];
//        $p_user = db('user')->where(['id'=>$pid])->find();
//        if($p_user['money'] - $fee < 0){
//            return $this->RSA_private_encrypt(error('余额不足'));
//        }
//
//        //db()->startTrans();
//        $info = db('user')->where(['id'=>$pid])->update(['money'=>$p_user['money']-$fee]);
//        $money_total = db('config')->where(['key'=>'money'])->find()['value'];
//        $info2 = db('config')->where(['key'=>'money'])->update(['value'=>$money_total+$fee]);
//
//        $info3 = $this->store_model->where(['id'=> $store['id']])->update(['status'=> 6, 'use_time'=> $this->date]);

        // 标记凭证已经使用


        // 记录日志
        $data = [
            'description'=> '用户获取凭证',
            'user_id'=> auth('port')->user()->id,
            'store_id'=> $store['id'],
        ];

        InoutLog::create($data);

        # 返回凭证
        # 看是箭头方式还是括号方式
        $encrypt = new TokenEncController();
        $data = [
            "id"=> $store['id'],
            "price"=> $store['price'],
            "desc"=> $store['description'],
            "status"=> $store['status'],  # 出库时判断状态吗？
            "start_time"=> $store['start_time'],
//            "end_time"=> $store['end_time'],
            "identifier"=> $store['identifier'],
            // "receipt"=> $store['receipt'],
            // "new_receipt"=> $store['new_receipt'],
            "receipt"=> $encrypt->token_public_decrypt($store['receipt']),
            "new_receipt"=> $encrypt->token_public_decrypt($store['new_receipt']),
        ];


        # 兼容老版本插件入库的凭证
        //...
        if (empty($data['receipt']) && !empty($data['new_receipt']))
        {
            if (mb_strlen($data['new_receipt']) > 3)
            {
                if (strtolower(mb_substr(trim($data['new_receipt'], 'utf8'),0,3)) == 'ewo') {
                    $data['receipt'] = $data['new_receipt'];
                }
            }
        }

        if (empty($data['new_receipt']) && !empty($data['receipt']))
        {
            if (mb_strlen($data['receipt']) > 3)
            {
                if (strtolower(mb_substr(trim($data['receipt'], 'utf8'),0,3)) == 'mii') {
                    $data['new_receipt'] = $data['receipt'];
                }
            }
        }
        //...


        return $this->RSA_private_encrypt(succ($data));
    }

    /**
     * 魔灵出库
     * @return string
     */
    public function vendre_info_one_moling()
    {

        # 验证账号出库权限
        if(auth('port')->user()->type == '入库'){
            return $this->RSA_private_encrypt(err('没有出库权限'));
        }

        $title = $this->param('title'); // 面值名

        if (empty($title))
        {
            return $this->RSA_private_encrypt(err('title length is 0'));
        }

        // 判断是否为魔灵
        $bundleIdentifier = $this->param('bundleIdentifier');
        if (empty($bundleIdentifier) || $bundleIdentifier != 'com.com2us.smon.normal.freefull.apple.kr.ios.universal')
        {
            return $this->RSA_private_encrypt(err('game is not allowed'));
        }

        // 获取支持面值
//        $map = [
//            'status'=> 1,
//            'title'=> $title,
//        ];

        $map = [
            ['status', '=', 1],
            ['title', '=', $title]
        ];

        $price = Price::where($map)->first();

        if (!$price)
        {
            return $this->RSA_private_encrypt(err('面值未开放'));
        }

        // 获取凭证
//        $map = [
////            'is_goods'=> 0,
//            'price_id'=> $price['id'],
//            'user_id'=> $this->user_id,
//        ];

        $map = [
            ['price_id', '=', $price['id']],
            ['user_id', '=', auth('port')->user()->id],
            ['status', '=', '6']
        ];

        $store = Store::where($map)->orderBy('id', 'asc')->first();

        if(!$store){
            $map = [
                ['price_id', '=', $price['id']],
                ['user_id', '=', auth('port')->user()->id],
            ];
            $in = [1, 5];

            $store = Store::whereIn('status', $in)->where($map)->orderBy('id', 'asc')->first();
        }

        // 跳过使用过的凭证

//        if ($userInfo['pass_store'] == 1)
//        {
//            $map['status'] = ['in', [1,5]];
//        }
//        else {
//            $map['status'] = ['in', [1, 5, 6]];
//        }

        if (!$store)
        {
            return $this->RSA_private_encrypt(err('凭证不存在'));
        }

//        $store = $store[0];

        //扣除手续费
        # 如果已经出库一次，则不在扣除手续费
        // 查询凭证的价格
//        $money = db('games_price')->where(['id'=>$store['price_id']])->find()['money'];
//        $percent = db('config')->where(['key'=>'info_one'])->find()['value'];
//        $fee = $money * $percent;
//
//        $son_id = $this->user_id;
//        $pid = db('user')->where(['id'=>$son_id])->find()['pid'];
//        $p_user = db('user')->where(['id'=>$pid])->find();
//        if($p_user['money'] - $fee < 0){
//            return $this->RSA_private_encrypt(error('余额不足'));
//        }
//
//        //db()->startTrans();
//        $info = db('user')->where(['id'=>$pid])->update(['money'=>$p_user['money']-$fee]);
//        $money_total = db('config')->where(['key'=>'money'])->find()['value'];
//        $info2 = db('config')->where(['key'=>'money'])->update(['value'=>$money_total+$fee]);
//
//        $info3 = $this->store_model->where(['id'=> $store['id']])->update(['status'=> 6, 'use_time'=> $this->date]);

        // 标记凭证已经使用

        // 记录日志
        $data = [
            'description'=> '用户获取凭证',
            'user_id'=> auth('port')->user()->id,
            'store_id'=> $store['id'],
        ];

        InoutLog::create($data);

        # 返回凭证
        # 看是箭头方式还是括号方式
        $encrypt = new TokenEncController();
        $data = [
            "id"=> $store['id'],
            "price"=> $store['price'],
            "desc"=> $store['description'],
            "status"=> $store['status'],  # 出库时判断状态吗？
            "start_time"=> $store['start_time'],
//            "end_time"=> $store['end_time'],
            "identifier"=> $store['identifier'],
            // "receipt"=> $store['receipt'],
            // "new_receipt"=> $store['new_receipt'],
            "receipt"=> $encrypt->token_public_decrypt($store['receipt']),
            "new_receipt"=> $encrypt->token_public_decrypt($store['new_receipt']),
        ];


        # 兼容老版本插件入库的凭证
        //...
        if (empty($data['receipt']) && !empty($data['new_receipt']))
        {
            if (mb_strlen($data['new_receipt']) > 3)
            {
                if (strtolower(mb_substr(trim($data['new_receipt'], 'utf8'),0,3)) == 'ewo') {
                    $data['receipt'] = $data['new_receipt'];
                }
            }
        }

        if (empty($data['new_receipt']) && !empty($data['receipt']))
        {
            if (mb_strlen($data['receipt']) > 3)
            {
                if (strtolower(mb_substr(trim($data['receipt'], 'utf8'),0,3)) == 'mii') {
                    $data['new_receipt'] = $data['receipt'];
                }
            }
        }
        //...


        return $this->RSA_private_encrypt(succ($data));
    }

    public function vendre_info_one_moling_bak()
    {

        $title = $this->param('title'); // 面值名

        if (empty($title))
        {
            return $this->RSA_private_encrypt(error('title length is 0'));
        }

        // 判断是否为魔灵
        $bundleIdentifier = $this->param('bundleIdentifier');
        if (empty($bundleIdentifier) || $bundleIdentifier != 'com.com2us.smon.normal.freefull.apple.kr.ios.universal')
        {
            return $this->RSA_private_encrypt(error('game is not allowed'));
        }





        // 获取支持面值
        $map = [
            'status'=> 1,
            'title'=> $title,
        ];


        $price = $this->games_price_model->where($map)->find();

        if (empty($price))
        {
            return $this->RSA_private_encrypt(error('面值未开放'));
        }

        // 获取凭证
        $map = [
            'is_goods'=> 0,
            'price_id'=> $price['id'],
            'user_id'=> $this->user_id,
        ];

        // 跳过使用过的凭证
        // $userInfo = $this->userInfo();

        //    if ($userInfo['pass_store'] == 1)
        //    {
        //      $map['status'] = ['in', [1,5]];
        //    }
        //   else {
        //       $map['status'] = ['in', [1, 5, 6]];
        //   }

        // 1. 首先出库手机端已获取

        $map['status'] = ['in', [6]];

        $store = $this->store_model->where($map)->order('id asc')->limit(1)->select();

        if (empty($store))
        {
            $map['status'] = ['in', [1,5]];
            $store = $this->store_model->where($map)->order('id asc')->limit(1)->select();
        }



        if (empty($store))
        {
            return $this->RSA_private_encrypt(error('凭证不存在'));
        }

        $store = $store[0];

        //扣除手续费
        // 查询凭证的价格
        $money = db('games_price')->where(['id'=>$store['price_id']])->find()['money'];
        $percent = db('config')->where(['key'=>'info_one'])->find()['value'];
        $fee = $money * $percent;

        $son_id = $this->user_id;
        $pid = db('user')->where(['id'=>$son_id])->find()['pid'];
        $p_user = db('user')->where(['id'=>$pid])->find();
        if($p_user['money'] - $fee < 0){
            return $this->RSA_private_encrypt(error('余额不足'));
        }

        //db()->startTrans();
        $info = db('user')->where(['id'=>$pid])->update(['money'=>$p_user['money']-$fee]);
        $money_total = db('config')->where(['key'=>'money'])->find()['value'];
        $info2 = db('config')->where(['key'=>'money'])->update(['value'=>$money_total+$fee]);

        $info3 = $this->store_model->where(['id'=> $store['id']])->update(['status'=> 6, 'use_time'=> $this->date]);

        // if($info && $info2 && $info3){
        //		db()->commit();
        // } else{
        // db()->rollback();
        //		return $this->RSA_private_encrypt(error('出库失败'));
        // }



        // 标记凭证已经使用


        // 记录日志
        $data = [
            'desc'=> '用户获取凭证',
            'user_id'=> $this->user_id,
            'store_id'=> $store['id'],
        ];

        $this->store_log_model->insert($data);

        // 返回凭证
        $data = [
            "id"=> $store['id'],
            "price"=> $store['price'],
            "desc"=> $store['desc'],
            "status"=> $store['status'],
            "start_time"=> $store['start_time'],
            "end_time"=> $store['end_time'],
            "identifier"=> $store['identifier'],
            // "receipt"=> $store['receipt'],
            // "new_receipt"=> $store['new_receipt'],
            "receipt"=> $this->token_public_decrypt($store['receipt']),
            "new_receipt"=> $this->token_public_decrypt($store['new_receipt']),
        ];


        // 兼容老版本插件入库的凭证
        //...
        if (empty($data['receipt']) && !empty($data['new_receipt']))
        {
            if (mb_strlen($data['new_receipt']) > 3)
            {
                if (strtolower(mb_substr(trim($data['new_receipt'], 'utf8'),0,3)) == 'ewo') {
                    $data['receipt'] = $data['new_receipt'];
                }
            }
        }

        if (empty($data['new_receipt']) && !empty($data['receipt']))
        {
            if (mb_strlen($data['receipt']) > 3)
            {
                if (strtolower(mb_substr(trim($data['receipt'], 'utf8'),0,3)) == 'mii') {
                    $data['new_receipt'] = $data['receipt'];
                }
            }
        }
        //...


        return $this->RSA_private_encrypt(succ($data));
    }

    /**
     * 出库-标记成功
     * @return string
     *
     */
    public function consumption()
    {
        $id = $this->param('id'); // 凭证ID

        if (empty($id))
        {
            return $this->RSA_private_encrypt(err('id length is 0'));
        }

        // 验证凭证是否未使用
//        $map = [
//            'id'=> $id,
//            'user_id'=> $this->user_id,
//        ];

        # 只根据凭证id就可以找到该凭证
        $map = [
            ['id', '=', $id],
//            ['owner_user_id', '=', auth('port')->user()->id],
//            ['user_type', '=', 2]
        ];

        $store = Store::where($map)->first();

        if ($store['status'] != 1 && $store['status'] != 5 && $store['status'] != 6)
        {
            return $this->RSA_private_encrypt(err('凭证状态错误'));
        }

//        if ($store['is_goods'])
//        {
//            return $this->RSA_private_encrypt(err('凭证已经发布到交易市场，无法出库'));
//        }

        // 修改凭证状态
        $data = [
            'status'=> 2,
            'use_time'=> date('Y-m-d H:i:s'),
        ];

        if (Store::where($map)->update($data))
        {
            // 获取有效同类型凭证数量
//            $map = [
////                'is_goods'=> 0,
//                'status'=> ['in', [1, 5]],
//                'price_id'=> $store['price_id'],
//                'owner_user_id'=> auth('port')->user()->id,
//            ];

            $map = [
                ['price_id', '=', $store['price_id']],
                ['owner_user_id', '=', auth('port')->user()->id]
            ];

            $count = Store::whereIn('status', [1, 5])->where($map)->count();

            // 记录日志
            $data = [
                'description'=> '标记凭证出库成功',
                'user_id'=> auth('port')->user()->id,
                'store_id'=> $store['id'],
            ];

            InoutLog::create($data);


            return $this->RSA_private_encrypt(succ('标记出库成功, 同类型凭证剩余 ' . $count . ' 个'));
        }
        else {
            return $this->RSA_private_encrypt(err('标记出库失败'));
        }
    }

    /**
     * 出库-凭证无效
     * @return string
     */
    public function invalid()
    {
        $id = $this->param('id'); // 凭证ID
//        $err_code = $this->param('err_code'); // 凭证ID
//        $err_msg = $this->param('err_msg'); // 凭证ID

        if (empty($id))
        {
            return $this->RSA_private_encrypt(err('id length is 0'));
        }

        // 验证凭证是否未使用
//        $map = [
//            'id'=> $id,
//            'user_id'=> $this->user_id,
//        ];

        # 只需要id就可以找到凭证
        $map = [
            ['id', '=', $id],
//            ['user_id', '=', auth('port')->user()->id]
        ];

        $store = Store::where($map)->first();

        # 只有状态5, 手机端已获取的才可以标记
        if ($store['status'] != 1 && $store['status'] != 5 && $store['status'] != 6)
        {
            return $this->RSA_private_encrypt(err('凭证状态错误'));
        }

//        if ($store['is_goods'])
//        {
//            return $this->RSA_private_encrypt(error('凭证已经发布到交易市场，无法出库'));
//        }

        // 修改凭证状态
        $data = [
            'status'=> 4,
            'use_time'=> date('Y-m-d H:i:s'),
        ];

        if (Store::where($map)->update($data))
        {
            // 记录日志
            $data = [
                'description'=> '标记凭证出库失败',
                'user_id'=> auth('port')->user()->id,
                'store_id'=> $store['id'],
            ];

            InoutLog::create($data);

            return $this->RSA_private_encrypt(succ('标记成功'));
        }
        else {
            return $this->RSA_private_encrypt(err('标记失败'));
        }
    }


    /**
     * 苹果内购验证
     * @param $receipt
     * @return mixed
     */
    protected function apple_verify($receipt){
        $url = 'https://buy.itunes.apple.com/verifyReceipt';

        $post = [
            'receipt-data' => $receipt,
        ];

        $post = json_encode($post);
        $buy = curl_request($url, $post);

        return json_decode($buy);
    }

    protected function currency(){
        return explode(',', Config::get_value('currency'));

    }
}
