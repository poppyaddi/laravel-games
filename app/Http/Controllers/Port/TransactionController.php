<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\ErrorStore;
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

        $map = [
            ['status', '=', 1],
            ['productIdentifier', '=', $productIdentifier]
        ];

        $game = Game::where($map)->first();

        if (!$game)
        {
            return $this->RSA_private_encrypt(err('不支持该游戏入库'));
        }

        $map = [
            ['status', '=', 1],
            ['game_id', '=', $game['id']]
        ];

        $data = Price::where($map)->withCount(['store' => function($query){
                    $map = [
                        ['owner_user_id', '=', auth('port')->user()->id],
                        ['user_type', '=', 2]
                    ];
                    $query->whereIn('status', [1, 5])->where($map);
                }])
                ->orderBy('money')
                ->get();

        return $this->RSA_private_encrypt(succ($data));
    }

    /**
     * 凭证入库
     * @return string
     */
    public function desmise_input()
    {
        # 判断操作账户类型(中间件中判断账户是否过期)

        $user = auth('port')->user();

        if($user->type == '出库'){
            return $this->RSA_private_encrypt(err('没有入库权限'));
        }

        $encrypt = new TokenEncController();

        # 1. 先接受凭证
        $transactionReceipt = $this->param('transactionReceipt'); // 凭证

        $localizedTitle = $this->param('localizedTitle') ?? ''; // 用于判断是否进行内购验证

        # 只有凭证是必须的
        if (empty($transactionReceipt))
        {
            return $this->RSA_private_encrypt(err('transactionReceipt length is 0'));
        }
        else
        {
            $receipt = $transactionReceipt;  # 苹果内购验证备用
            $transactionReceipt = $encrypt->token_private_encrypt($transactionReceipt); # 入库使用
        }

        # 2. 验证凭证重复 用md5($receipt)验证
        $enc = md5($receipt);
        $info = Store::where('enc', $enc)->first();

        # 2.1 错误日志
        $error['receipt'] = $receipt;
        $error['gold'] = $localizedTitle;
        $error['user_id'] = $user->id;  # 子账户id
        $error['parent_id'] = $this->parent()->id;

        if ($info)
        {
            $error['description'] = '凭证重复';
            ErrorStore::create($error);

            return $this->RSA_private_encrypt(err('凭证重复'));
        }

        # 3. 验证凭证, 并获取苹果内购验证
        # 3.1 获取不需验证的面值
        $passed_price_title = Price::where('pass', 1)->pluck('title')->toArray();
        # 不是跳过的凭证则需要进行苹果内购验证
        $res = (object) [];
        if(!in_array($localizedTitle, $passed_price_title)){
            $res = $this->apple_verify($receipt);

            if($res->status != 0 || $res->status == 21002){
                # 记录错误凭证

                $error['description'] = '凭证验证失败';
                ErrorStore::create($error);

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
                $error['description'] = '未知错误，凭证更新?';
                ErrorStore::create($error);

                return $this->RSA_private_encrypt(err('0002')); # 未知错误
            }

            $buyTime = substr($buyTime, 0, 10);  // 真实购买时间
            $offset =  time() - $buyTime;

            $expire_time = Config::get_value('receipt_expire_time');
            if($offset > $expire_time){
//            $t = date('Y-m-d H:i:s', $buyTime);
//            $message = "购买时间过期：" . $t;
                $error['description'] = '凭证过期';
                ErrorStore::create($error);

                return $this->RSA_private_encrypt(err('0003')); # 凭证过期
            }
        }


        # 4. 处理其他参数
        $transactionIdentifier = $this->param('transactionIdentifier', true) ?? $res->receipt->transaction_id; // 订单号
        $transactionDate = $this->param('transactionDate', true) ?? date('Y-m-d H:i:s',     substr($res->receipt->original_purchase_date_ms, 0, 10)); // 生成日期

        $newTransactionReceipt = $this->param('newTransactionReceipt'); // 新凭证

        $localizedPrice = $this->param('localizedPrice', true) ?? $res->receipt->product_id; // 面值

        $localizedDescription = $this->param('localizedDescription', true) ?? '无描述'; // 描述

        $productIdentifier = $this->param('productIdentifier', true) ?? $res->receipt->bid; // 包名

         $currency =  $this->param('currency', true) ?? '没有币种';

        # 验证币种
        if(in_array($currency, $this->currency())){
            $error['description'] = '币种不符合要求';
            ErrorStore::create($error);

            return $this->RSA_private_encrypt(err('币种不符合要求'));
        }

        # 验证游戏类型是否支持
        $map = [
            ['productIdentifier', '=', $productIdentifier ?? $res->receipt->bid],
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

        # $localizedTitle 识别单个面值标识

        # return $this->RSA_private_encrypt(error($localizedTitle));

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
            'input_user_id' => $user->id,
            'owner_user_id' => $user->id,
            'currency'      => $currency,
            'enc'           => $enc,
            'created_at'    => now(),
            'input_parent_id' => $this->parent()->id # 入库账号的父账号id

        ];

        if (!empty($newTransactionReceipt))
        {
            $data['new_receipt'] = $encrypt->token_private_encrypt($newTransactionReceipt);
        }

        $store_id = Store::insertGetId($data);

        if ($store_id)
        {
            // 记录凭证
            $data = [
                'description'=> '手机用户入库',
                'user_id'=> auth('port')->user()->id,
                'store_id'=> $store_id,
                'type' => 1
            ];

            # 插入日志
            InoutLog::create($data);

            return $this->RSA_private_encrypt(succ('入库成功'));
        }
        else {
            return $this->RSA_private_encrypt(err('入库失败, 请稍后再试'));
        }
    }

    public function desmise_input_bak()
    {
        # 判断操作账户类型(中间件中判断账户是否过期)

        if(auth('port')->user()->type == '出库'){
            return $this->RSA_private_encrypt(err('没有入库权限'));
        }

        $encrypt = new TokenEncController();

        # 1. 先接受凭证
        $transactionReceipt = $this->param('transactionReceipt'); // 凭证

        # 只有凭证是必须的
        if (empty($transactionReceipt))
        {
            return $this->RSA_private_encrypt(err('transactionReceipt length is 0'));
        }
        else
        {
            $receipt = $transactionReceipt;  # 苹果内购验证备用
            $transactionReceipt = $encrypt->token_private_encrypt($transactionReceipt); # 入库使用
        }

        # 2. 判断



        # 处理参数
        $transactionIdentifier = $this->param('transactionIdentifier'); // 订单号
        $transactionDate = $this->param('transactionDate'); // 生成日期

        $newTransactionReceipt = $this->param('newTransactionReceipt'); // 新凭证

        $localizedPrice = $this->param('localizedPrice'); // 面值
        $localizedTitle = $this->param('localizedTitle'); // 标题
        $localizedDescription = $this->param('localizedDescription'); // 描述

        $productIdentifier = $this->param('productIdentifier'); // 包名

        $currency =  $this->param('currency') ?? '没有币种';




//        if (empty($transactionIdentifier))
//        {
//            return $this->RSA_private_encrypt(err('transactionIdentifier length is 0'));
//        }
//
//        if (empty($transactionDate))
//        {
//            return $this->RSA_private_encrypt(err('transactionDate length is 0'));
//        }



//        if (empty($newTransactionReceipt))
//        {
//            return $this->RSA_private_encrypt(err('newTransactionReceipt length is 0'));
//        }
//        else
//        {
//            $newTransactionReceipt = $encrypt->token_private_encrypt($newTransactionReceipt);
//        }
//
//        if (empty($localizedPrice))
//        {
//            return $this->RSA_private_encrypt(err('price length is 0'));
//        }
//
//        if (empty($localizedTitle))
//        {
//            return $this->RSA_private_encrypt(err('localizedTitle length is 0'));
//        }
//
//        if (empty($localizedDescription))
//        {
//            return $this->RSA_private_encrypt(err('localizedDescription length is 0'));
//        }
//
//        if (empty($productIdentifier))
//        {
//            return $this->RSA_private_encrypt(err('productIdentifier length is 0'));
//        }
//
//        if (empty($encrypt))
//        {
//            return $this->RSA_private_encrypt(err('encrypt length is 0'));
//        }

        # 验证币种
        if(in_array($currency, $this->currency())){
            return $this->RSA_private_encrypt(err('币种不符合要求'));
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

        # 验证凭证重复 用md5($receipt)验证
        $enc = md5($receipt);
        $info = Store::where('enc', $enc)->first();

//        $info = Store::where('identifier', $transactionIdentifier)
//                ->orWhere('receipt', $transactionReceipt)
//                ->first();
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
            'input_user_id' => auth('port')->user()->id,  # 入库账号的父账号id
            'input_parent_id' => $this->parent()->id,  # 入库账号的父账号id
            'owner_user_id' => auth('port')->user()->id,
            'currency'      => $currency,
            'end'           => $enc

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

        $user = auth('port')->user();

        # 验证账号出库权限
        if($user->type == '入库'){
            return $this->RSA_private_encrypt(err('没有出库权限'));
        }

        $title = $this->param('title'); // 面值名

        if (empty($title))
        {
            return $this->RSA_private_encrypt(err('title length is 0'));
        }

        $map = [
            ['status', '=', 1],
            ['title', '=', $title]
        ];

        $price = Price::where($map)->first();

        if (!$price)
        {
            return $this->RSA_private_encrypt(err('面值未开放'));
        }

        $map = [
            ['price_id', '=', $price['id']],
            ['owner_user_id', '=', $user->id],
            ['user_type', '=', 2]
        ];

        // 是否跳过使用过的凭证
        $in = $this->parent()->userinfo->pass_store == 1 ? [1, 5] : [1, 5, 6];

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

        # 将出库次数加1


        // 记录日志
        $data = [
            'description'=> '用户手机端获取凭证',
            'user_id'=> $user->id,
            'store_id'=> $store['id'],
            'type' => 2
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
            "identifier"=> $store['identifier'],
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

        # 标记凭证出库
        Store::where('id', $store['id'])->update(['status'=>6, 'use_time'=>now(), 'consump_num'=>$store['consump_num'] + 1]);

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

        $map = [
            ['status', '=', 1],
            ['title', '=', $title]
        ];

        $price = Price::where($map)->first();

        if (!$price)
        {
            return $this->RSA_private_encrypt(err('面值未开放'));
        }

        $map = [
            ['price_id', '=', $price['id']],
            ['owner_user_id', '=', auth('port')->user()->id],
            ['status', '=', '6'],
            ['user_type', '=', 2]
        ];

        $store = Store::where($map)->orderBy('id', 'asc')->first();

        if(!$store){
            $map = [
                ['price_id', '=', $price['id']],
                ['owner_user_id', '=', auth('port')->user()->id],
                ['user_type', '=', 2]
            ];
            $in = [1, 5];

            $store = Store::whereIn('status', $in)->where($map)->orderBy('id', 'asc')->first();
        }


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
//            return $this->RSA_private_encrypt(err('余额不足'));
//        }
//
//        //db()->startTrans();
//        $info = db('user')->where(['id'=>$pid])->update(['money'=>$p_user['money']-$fee]);
//        $money_total = db('config')->where(['key'=>'money'])->find()['value'];
//        $info2 = db('config')->where(['key'=>'money'])->update(['value'=>$money_total+$fee]);
//
//        $info3 = $this->store_model->where(['id'=> $store['id']])->update(['status'=> 6, 'use_time'=> $this->date]);

        # 标记凭证出库
        Store::where('id', $store['id'])->update(['status'=>6, 'use_time'=>now(), 'consump_num'=>$store['consump_num'] + 1]);

        // 记录日志
        $data = [
            'description'=> '用户获取凭证',
            'user_id'=> auth('port')->user()->id,
            'store_id'=> $store['id'],
            'type' => 2
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
            "identifier"=> $store['identifier'],
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
            ['owner_user_id', '=', auth('port')->user()->id],
            ['user_type', '=', 2]
        ];

        $store = Store::where($map)->first();

        if ($store['status'] != '手机端已获取')
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
                ['owner_user_id', '=', auth('port')->user()->id],
                ['user_type', '=', 2]
            ];

            $count = Store::whereIn('status', [1, 5])->where($map)->count();

            // 记录日志
            $data = [
                'description'=> '标记凭证出库成功',
                'user_id'=> auth('port')->user()->id,
                'store_id'=> $store['id'],
                'type' => 2
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
                'type' => 2
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
    public function apple_verify($receipt){
        $url = 'https://buy.itunes.apple.com/verifyReceipt';

        $post = [
            'receipt-data' => $receipt,
        ];

        $post = json_encode($post);
        $buy = curl_request($url, $post);

        return json_decode($buy);
    }

    protected function currency(){
        $currency = Config::get_value('currency');

        return $currency ? explode(',', $currency) : [];

    }
}
