<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\CzGame;
use App\Models\Device;
use App\Models\Game;
use App\Models\Price;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Port\BaseController;

class TestController extends BaseController
{
    //
    public function test()
    {
//        $map = [
//            'status'=> 1,
//            'productIdentifier'=> 'com.special.warships.',
//        ];
//
//        $game = Game::where($map)->first();
//        return $game;

//        $user_id = auth('port')->user()->user_id;
//        $user_info = User::where('users.id', $user_id)->with('userinfo')->first();
//        return $user_info;
//        $info = User::where('id', 100)->first();
//        if($info){
//            return 2;
//        } else{
//            return 3;
//        }
//        $info = User::where('id', '<', 100)->orderBy('id')->get();
//        $info = Role::withCount(['user'=>function($query){
//            return $query->where([['users.id', 'in', (10)]]); # 关联模型添加条件, 获取的count数量减少
//        }])
////            ->select('id', 'name', 'count')
//            ->get();
//        $info = date('Y-m-d H:i:s');
//        return $info;

//        Cache::set('')
//        $data = DB::connection("encrypt")->table("ciphertext")->where('key_name', 'private_key')->first();
//
//        $value = Cache::remember('private_key', 1800, function () {
//            $key =  DB::connection("encrypt")->table("ciphertext")->where('key_name', 'private_key')->first();
//            return $key->value;
//        });
//        Cache::forget('private_key');

//        return $this->get__key('private_key');

//        $data = 'hello world';
//
//        $privateKey = $this->get__key('private_key');
//
//        $crypto = '';
//
//        foreach (str_split($data, 117) as $chunk)
//        {
//            openssl_private_encrypt($chunk, $encrypted, $privateKey); // 私钥加密
//
//            $crypto .= $encrypted;
//        }
//
//        $crypto =  base64_encode($crypto);
//
//        $publicKey = $this->get__key('public_key');
//
//        $data = base64_decode($crypto);
//        $crypto = '';
//
//        foreach (str_split($data, 256) as $chunk)
//        {
//            openssl_public_decrypt($chunk, $decryptData, $publicKey);
//            $crypto .= $decryptData;
//        }
//
//        return $crypto;

//        $passed_price = Price::where('pass', 1)->pluck('title')->toArray();
//        if(in_array('34567', $passed_price)){
//            return 1;
//        } else{
//            return 2;
//        }
//        return $passed_price;
        $url = 'https://buy.itunes.apple.com/verifyReceipt';
//        $post = [
//            'productIdentifier' => $productIdentifier,  # 游戏包名
//            'state' => 'Purchased',
//            'transactionIdentifier' => $transactionIdentifier,
//            'receipt-data' => $receipt,
//
//        ];



//        $post = [
//            'receipt-data' => $receipt,
//
//        ];
//
//        $post = json_encode($post);
//        $buy = curl_request($url, $post);
//
//        $res = json_decode($buy);
//        if($res->status != 0 || $res->status == 21002){
//            return 1;  # 凭证验证失败
//            die;
//        }
//
//        $str = substr($receipt, 0, 3);
//        if($str == 'ewo'){
//            $buyTime = $res->receipt->purchase_date_ms;
//            # return $buyTime;  # 1569118165963  精确到毫秒
//        } elseif($str == 'MII'){
//            $buyTime = $res->receipt->in_app[0]->purchase_date_ms;
//            # return $buyTime;  # 1569118165000
//        } else{
//            return 2; # 为止错误
//        }
//
//        $buyTime = substr($buyTime, 0, 10);  // 真实购买时间
//        $offset =  time() - $buyTime;
//        // return $this->RSA_private_encrypt(error(time()));
//
//        $expire_time = Config::get_value('receipt_expire_time');
//        if($offset > $expire_time){
////            $t = date('Y-m-d H:i:s', $buyTime);
////            $message = "购买时间过期：" . $t;
//            return 3;  # 时间过期
//        }
//        $user_id    = auth('port')
//            ->user()
//            ->user_id;
//        $user_info  = User::where('users.id', $user_id)
//            ->with('userinfo')
//            ->first();
//        return $user_info;

//        $currency = Config::get_value('currency');
//        $currency = explode(',', $currency);
//        return $currency;

//        return auth('port')->user()->type;
//        $map = [
//            ['status', '=', 1],
//            ['title', '=', '2345有']
//        ];
//
//        $price = Price::where($map)->first();
//        if($price){
//            return 1;
//        } else{
//            return 2;
//        }
//        $in = $this->parent()->userinfo->pass_store == 1 ? [1, 5] : [1, 5, 6];
//        return $in;

//        $game = Game::where('id', '>', 2)->first();
//        return $game['name'];

//        $key =  DB::connection("temp")
//            ->table("ciphertext")
//            ->where('key_name', $key)
//            ->first();
        /**
         *
        $data = CzGame::with('price')->get();
        $count = 0;
        echo date('Y-m-d H:i:s');
        foreach ($data as $val){
            $game['name'] = $val['gs_name'];
            $game['productIdentifier'] = $val['productIdentifier'];
            $game['description'] = '数据复制添加';
            $game_id = Game::insertGetId($game);
//            dump($game);
            foreach ($val->price as $value){
                $price['gold'] = $value['gold'];
                $price['money'] = $value['money'];
                $price['title'] = $value['title'];
                $price['game_id'] = $game_id;
                Price::create($price);
            }
            echo '.........完成第' . $count++ . '行.........';
            echo '<br>';
        }
        echo '.........完成插入..........';
        echo date('Y-m-d H:i:s');
         *
         */
//        return auth('port')->user();
//        $credentials = request()->only('name', 'password');
////        return $credentials;
//        if (! $token = auth('port')->attempt($credentials)) {
//            return 3;
//        }
//        return auth('port')->user();
//        $data = CzGame::get();
////        return $data;
//        $count = 0;
//        foreach ($data as $val){
//            $device['device'] = $val['device'];
//            $device['son_id'] = 5;
//            $device['status'] = '1';
//            Device::create($device);
//            echo '.............' . $count++ . '...........';
//            echo '<br>';
//        }
//        echo 'finished';

//        return $this->parent()->userinfo->save_device;

//        $data = [
//            'device' => 'fsdfsaf',
//            'son_id' => 3,
//            'status' => 1
//        ];
//
//        $info = Device::create($data);
//        return $info->id;
//        return $this->parent();
        return $this->parent();
            $expire_time = $this->parent()->userinfo->expire_time;
        if(strtotime($expire_time) < time()){
            return '账号过期';
        } else{
            return '不过期';
        }
        return $this->parent();

    }


    public function apple_verity($receipt)
    {
        $url = 'https://buy.itunes.apple.com/verifyReceipt';

        $post = [
            'receipt-data' => $receipt,
        ];

        $post = json_encode($post);
        $buy = curl_request($url, $post);

        $res = json_decode($buy);
        if($res->status != 0 || $res->status == 21002){
            return 1;  # 凭证验证失败
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
            return 2; # 为止错误
        }

        $buyTime = substr($buyTime, 0, 10);  // 真实购买时间
        $offset =  time() - $buyTime;

        $expire_time = Config::get_value('receipt_expire_time');
        if($offset > $expire_time){
//            $t = date('Y-m-d H:i:s', $buyTime);
//            $message = "购买时间过期：" . $t;
            return 3;  # 时间过期
        }
    }




    protected function get__key($key){
        return Cache::remember($key, 1800, function () use ($key) {
            $key =  DB::connection("encrypt")->table("ciphertext")->where('key_name', $key)->first();
            return $key->value;
        });
    }


}
