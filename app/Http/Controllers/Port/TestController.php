<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\CzGame;
use App\Models\Device;
use App\Models\Game;
use App\Models\InoutLog;
use App\Models\Price;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Port\BaseController;

class TestController extends Controller
{

//    //
    protected $rsa_path = '';
    protected $raw = [];
//    //
    public function __construct()
    {
        $this->rsa_path = public_path() . DIRECTORY_SEPARATOR;
//        $this->get_raw();
    }

    public function support()
    {
        $productIdentifier = $this->param('productIdentifier'); // 包名

        if (empty($productIdentifier))
        {
            return $this->RSA_private_encrypt(err('productIdentifier length is 0'));
        }

        // 获取游戏
//        $map = [
//            'status'=> 1,
//            'productIdentifier'=> $productIdentifier,
//        ];

        $map = [
            ['status', '=', 1],
            ['productIdentifier', '=', $productIdentifier]
        ];

        $game = Game::where($map)->first();

        if ($game)
        {
//            $data = [
//                'message'=> '支持这个游戏',
//                'receipt_type'=> $game['receipt_type'],
//            ];
//
//            return $this->RSA_private_encrypt(succ($data));
            $data = [
                'code'=> 200,
                'auth'=> true,
                'data'=> (object) [],
                'message'=> '支持这个游戏',
//                'receipt_type'=> $game['receipt_type'],
            ];

            return $this->RSA_private_encrypt(succ($data));
        }
        else {
            return $this->RSA_private_encrypt(err('不支持这个游戏'));
        }
    }

    public function decrypt ()
    {

//        return 1;
//        echo 1;
        $data = 'gu1LTocf3SeGoZdoqVBm4qqTSc+zHrIWqnBnYP6RPuLWivOvDCU1m9YWjS6QyjEljFf11VQG3v8Qor7D/GCgSy5b78GOu4eAh40pLS7qqvT5FPZaZ6S6m73+s89zfxytCO7rhAgmv+12GczXB4U5BVZCseIfyMuceqrXyOkhbGg=';
        $res = $this->RSA_public_decrypt($data, 1);
        return success($res);
    }
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
//        return $info;
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

//        $page = 1;
//        $pageSize = 10;
////        $count = CzGame::count();
//
//        $user_id = auth('port')->user()->id;
//
////        for($i=1; $i<=30; $i++){
//
//            $data = CzGame::with(['price'=>function($query){
//                return $query->with('store');
//            }])
//                ->offset(30 * $pageSize)
//                ->limit($pageSize)
//                ->get();
//
//
//            $count = 0;
//            echo date('Y-m-d H:i:s');
//            foreach ($data as $val){
//                $game['name'] = $val['gs_name'];
//                $game['productIdentifier'] = $val['productIdentifier'];
//                $game['description'] = '数据复制添加';
//                $game_id = Game::insertGetId($game);
//                foreach ($val->price as $value){
//                    $price['gold'] = $value['gold'];
//                    $price['money'] = $value['money'];
//                    $price['title'] = $value['title'];
//                    $price['game_id'] = $game_id;
//                    $price_id = Price::insertGetId($price);
//
//                    foreach ($value->store as $sss){
//                        $store['price'] = $sss->price;
//                        $store['identifier'] = $sss->identifier;
//                        $store['receipt'] = $sss->receipt;
//                        $store['new_receipt'] = $sss->new_receipt;
//                        $store['currency'] = 'CNY';
//                        $store['game_id'] = $game_id;
//                        $store['price_id'] = $price_id;
//                        $store['description'] = '同步数据';
//                        $store['start_time'] = now();
//                        $store['status'] = $sss->status;
//                        $store['input_user_id'] = $user_id;
//                        $store['owner_user_id'] = $user_id;
//                        Store::create($store);
//                    }
//                }
//                echo '<br>';
//                echo '.........完成第' . $count++ . '行.........';
//                echo '<br>';
//            }
//            echo '.........完成插入..........';
//
//            echo date('Y-m-d H:i:s');
//            echo '<br>';



//        }

//        return $data;

//        die;


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
//        return $this->parent();
//            $expire_time = $this->parent()->userinfo->expire_time;
//        if(strtotime($expire_time) < time()){
//            return '账号过期';
//        } else{
//            return '不过期';
//        }
//        return $this->parent();

//        $data = CzGame::with('price')->get();

//        return CzGame::with('price')->select('gs_id')->get();
//
//        return $data;

//        $a = md5('hello world');
//        echo $a;

//        Store::update();
//        $lists = Store::select('id', 'receipt')->get();
//
//        foreach($lists as $list){
//            try{
//                Store::where('id', $list->id)->update(['enc'=>md5($list->receipt)]);
//            } catch (\PDOException $e){
//                continue;
//            }
//        }
//        echo 'finished';

        return sha1('hello');

        $map = [
            ['user_type', '=', 2],
            ['status', '=', 2]
        ];
        $logs = Store::where($map)->select('id', 'owner_user_id', 'description')->get();
        $count = 0;
        foreach ($logs as $log){
            $data = [
                'user_id'=>$log->owner_user_id,
                'store_id'=>$log->id,
                'description'=>$log->description
            ];
            InoutLog::create($data);
            echo "<br>";
            echo $count++ . '行';
            echo "<br>";
        }
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





    public function index()
    {
        return ['code'=>20000, 'data'=>'admin-token'];
    }

    protected function param($name, $allow_null=false)
    {
        if (empty($name))
        {
            echo $this->RSA_private_encrypt(err('参数名为空'));
            exit;
        }

        if (empty($this->raw->$name))
        {
            if ($allow_null == true)
            {
                return '';
            }

            echo $this->RSA_private_encrypt(err($name . ' is null'));
            exit;
        }

        return $this->raw->$name;
    }

    /*
     * raw 参数处理
     * */
    protected function get_raw()
    {
        // 接收参数
        $data = trim(file_get_contents('php://input'));


        if (empty($data))
        {
            echo $this->RSA_private_encrypt(err('参数为空'));
            exit;
        }

        // 解密参数
        $raw = $this->RSA_public_decrypt($data);

        if (empty($raw))
        {
            echo $this->RSA_private_encrypt(err('数据错误'));
            exit;
        }

        if (is_string($raw))
        {
            $raw = json_decode($raw);
        }

        $this->raw = $raw;
    }

    /*
     * RSA 私钥加密[默认,服务端]
     * */
    public function RSA_private_encrypt($data, $type=1)
    {
        $data = json_encode($data);
        $crypto = '';

        foreach (str_split($data, 117) as $chunk)
        {
            openssl_private_encrypt($chunk, $encrypted, $this->getPrivate($type)); // 私钥加密

            $crypto .= $encrypted;
        }

        return base64_encode($crypto);
    }

    /*
     * RSA 公钥解密[默认,客户端]
     * */
    protected function RSA_public_decrypt($data, $type=2)
    {
        $data = base64_decode($data);

        $crypto = '';

        foreach (str_split($data, 128) as $chunk)
        {
            openssl_public_decrypt($chunk, $decryptData, $this->getPublic($type)); // 使用私匙解密

            $crypto .= $decryptData;
        }

        return json_decode($crypto);
    }

    /*
     * 读取公钥
     * */
    protected function getPublic($type=1)
    {
        $key = $type == 1 ? config('rsa_key.server_public_key') : config('rsa_key.client_public_key');

        $public_key = file_get_contents($this->rsa_path . $key);

        if (empty($public_key))
        {
            echo $this->RSA_private_encrypt(err("公钥文件不存在"));
            exit;
        }

        $pu_key = openssl_pkey_get_public($public_key);

        if (empty($pu_key))
        {
            echo $this->RSA_private_encrypt(err("公钥读取失败"));
            exit;
        }

        return $pu_key;
    }

    /*
     * 读取私钥
     * */
    protected function getPrivate($type=1)
    {
        $key = $type == 1 ? config('rsa_key.server_private_key') : config('rsa_key.client_private_key');

        $private_key = file_get_contents($this->rsa_path . $key);

        if (empty($private_key))
        {
            echo $this->RSA_private_encrypt(err("私钥文件不存在"));
            exit;
        }

        $pi_key = openssl_pkey_get_private($private_key);

        if (empty($pi_key))
        {
            echo $this->RSA_private_encrypt(err("私钥读取失败"));
            exit;
        }

        return $pi_key;
    }


}
