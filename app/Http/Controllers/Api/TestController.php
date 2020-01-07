<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CzGame;
use App\Models\CzStore;
use App\Models\Game;
use App\Models\Price;
use App\Models\Store;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    //
    public function test(Request $request)
    {
//        $user = auth('api')->user();
//        return $user_info = UserInfo::where('user_id', $user->id)->first();
//        date_default_timezone_set('PRC');
        $a =[1, 2, 3];
        $b = [2, 3, 4];
        $c = array_merge($a, $b);
        return $c;
    }

    /**
     * 游戏，面值导入
     */
    public function sync_game()
    {
//        return 'hello world';
            $data = CzGame::with(['price'=>function($query){
                return $query->select('id', 'gs_id', 'money', 'title', 'gold');
            }])
                ->get();

            $count = 1;
            foreach ($data as $game){
                # 插入游戏
                $game_data['name'] = $game['gs_name'];
                $game_data['productIdentifier'] = $game['productIdentifier'];
                $game_data['status'] = 1;
                $game_data['description'] = '电脑同步';
                $game_data['created_at'] = now();
//                return $game_data;break;
                # 检查数据库是否有已有该游戏标识，如果有则跳过
                $flag = Game::where('productIdentifier', $game['productIdentifier'])->count();
                if($flag){
                    continue;
                }
                $game_id = Game::insertGetId($game_data);

                foreach ($game->price as $price){
                    $price_data['gold'] = $price['gold'];
                    $price_data['title'] = $price['title'];
                    $price_data['money'] = $price['money'];
                    $price_data['game_id'] = $game_id;
                    $price_data['status'] = 1;
                    # 查看数据库是否有该面值
                    $flag = Price::where(['game_id'=>$game_id, 'title'=>$price['title']])->count();
                    if($flag){
                        continue;
                    }
                    Price::create($price_data);

                    echo $count++ . '<br>';
                }

            }

    }

    /**
     * 凭证导入
     */
    public function sync_store()
    {
//        return 1;
        $start = 0;
        $end = 1000;

        $data = CzStore::join('cz_games_price', 'cz_store.price_id', '=', 'cz_games_price.id')->join('cz_games', 'cz_games.gs_id', '=', 'cz_games_price.gs_id')->whereIn('cz_store.status', [1, 5, 6])->get();
//        return $data;

        DB::transaction(function () use ($data) {

            $count = 1;
            foreach($data as $store){
                # 查找game_id
                $game_id = Game::where('productIdentifier', $store['productIdentifier'])->first()->id;
                $price_id = Price::where(['title'=>$store['title'], 'game_id'=>$game_id])->first()->id;

                $store_data['price'] = $store['price'];
                $store_data['identifier'] = $store['identifier'];
                $store_data['receipt'] = $store['receipt'];
                $store_data['new_receipt'] = $store['new_receipt'];
                $store_data['description'] = $store['desc'] . '---电脑同步';
                $store_data['game_id'] = $game_id;
                $store_data['price_id'] = $price_id;
                $store_data['input_user_id'] = 13;
                $store_data['owner_user_id'] = 1;
                $store_data['user_type'] = 1;
                $store_data['status'] = 1;
                $store_data['enc'] = $store['receipt_token'];

                Store::create($store_data);
                echo '插入' . $count++ . '\r\n';
            }

        });


    }
}
