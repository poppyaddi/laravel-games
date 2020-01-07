<?php

namespace App\Imports;

use App\Http\Controllers\Port\TokenEncController;
use App\Models\Config;
use App\Models\Game;
use App\Models\InoutLog;
use App\Models\Price;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\UserLog;

class StockImport implements ToCollection
{

//    public function model(array $row)
//    {
//        return new Store([
//            //
//        ]);
//    }
    public function collection(Collection $rows)
    {


//
        $receipt_position = Config::get_value('receipt_position');
        $new_receipt_position = Config::get_value('new_receipt_position');
        $input_user_id = Config::get_value('input_user_id');

        $encrypt = new TokenEncController();

        DB::beginTransaction();
        $r = [];
        foreach ($rows as $row) {

//            # 如果有其他sheet为空，直接跳过
            if ($row[0] == null) {
                continue;
                break;
            }

//            # 获取凭证
            $receipt = $row[$receipt_position];
            $new_receipt = $row[$new_receipt_position];
//            dump($receipt);die;

            $apple_verify = apple_verify($receipt);

            # 是否跳过错误
//            if($apple_verify->status != 0 || $apple_verify->status == 21002){
//                continue;
//            }

            $price_title = $apple_verify->receipt->product_id;
            $game_identifier = $apple_verify->receipt->bid;

            # 获取游戏id
            $game_id = Game::where('productIdentifier', $game_identifier)->first()->id;
            $price = Price::where('title', $price_title)->first();

            # md5哈希使用原始凭证
            $enc = md5($receipt);

            $data = [
                'price' => $price->money,
                'description' => 'excel导入',
                'game_id' => $game_id,
                'price_id' => $price->id,
                'status' => 1,
                'start_time' => date('Y-m-d H:i:s', substr($apple_verify->receipt->original_purchase_date_ms, 0, 10)),
                'identifier' => $apple_verify->receipt->transaction_id,
                'receipt' => $encrypt->token_private_encrypt($receipt),
                'input_user_id' => $input_user_id,
                'owner_user_id' => 1,
                'user_type' => 1,
                'currency' => '无',
                'enc' => $enc,  # 对原始凭证进行的加密
                'created_at' => now(),
                'input_parent_id' => 1 # 入库账号的父账号id

            ];

            if (!empty($new_receipt))
            {
                $data['new_receipt'] = $encrypt->token_private_encrypt($new_receipt);
            }

            $store_id = Store::insertGetId($data);
            $r[] = $store_id;

            if ($store_id) {
                // 记录凭证
                $data = [
                    'description' => '电脑端导入, 子账户为临时账户',
                    'user_id' => $input_user_id,
                    'store_id' => $store_id,
                    'type' => 1
                ];
                # 插入日志
                InoutLog::create($data);
            }
        }

        if(!in_array(false, $r)){
            DB::commit();
        } else{
            DB::rollBack();
        }
    }
}
