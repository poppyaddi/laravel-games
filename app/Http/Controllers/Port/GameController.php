<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Price;
use Illuminate\Http\Request;
use App\Http\Controllers\Port\Rsa1024Controller;

class GameController extends Rsa1024Controller
{
    //
    /**
     * 根据游戏包名获取支持的面值
     * @return string
     */
    public function price()
    {
        $productIdentifier = $this->param('productIdentifier'); // 游戏包名

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

        if (!$game)
        {
            return $this->RSA_private_encrypt(err('不支持该游戏入库'));
        }

        // 获取支持面值
//        $map = [
//            'status'=> 1,
//            'game_id'=> $game['id'],
//        ];

        $map = [
            ['status', '=', 1],
            ['game_id', '=', $game['id']]
        ];

        # 获取该游戏下支持的面值
        $data = Price::where($map)->select('id', 'gold', 'title', 'money')->get();

//        $data = [];
//
//        foreach ($price as $key=> $value)
//        {
//            $item = [];
//            $item['id'] = $value['id'];
//            $item['title'] = $value['title'];
//            $item['money'] = number_format($value['money']) . '元';
//            $item['gold'] = $value['gold'];
//
//            $data[] = $item;
//        }


        return $this->RSA_private_encrypt(succ($data));
    }

    /**
     * 添加游戏及面值
     * @return string
     */
    public function addGame()
    {
        $gs_name = $this->param('gs_name'); // 游戏名称
        $productIdentifier = $this->param('productIdentifier'); // 游戏包名
        $money = $this->param('money'); // 人民币
        $gold = $this->param('gold'); // 金币
        $title = $this->param('title'); // 面值标识

        # 根据子账户获取其父账户信息
        if ($this->parent()->userinfo->admin != 1 )
        {
            return $this->RSA_private_encrypt(err('您没有添加面值的权限'));
        }

        // 验证游戏是否已经存在
//        $map = [
//            'productIdentifier'=> $productIdentifier,
//        ];

        $map = [
            ['productIdentifier', '=', $productIdentifier]
        ];

        $game = Game::where($map)->first();

        if (!$game)
        {
            $data = [
                'name'=> $gs_name,
                'description'=> '手机添加，用户主账户名称: ' . $this->parent()->name,
                'productIdentifier'=> $productIdentifier,
            ];

            $game_id = Game::insertGetId($data);

            if (!$game_id)
            {
                return $this->RSA_private_encrypt(err('添加游戏失败'));
            }
        }
        else {
            $game_id = $game['id'];
        }

        // 验证面值是否已经存在
//        $map = [
//            'title'=> $title,
//        ];

        $map = [
            ['title', '=', $title]
        ];

        $price = Price::where($map)->first();

        if (!$price)
        {
            $data = [
                'game_id'=> $game_id,
                'money'=> $money,
                'gold'=> $gold,
                'title'=> $title,
            ];

            $price_id = Price::insertGetId($data);

            if ($price_id)
            {
                return $this->RSA_private_encrypt(succ('面值添加成功'));
            }
            else {
                return $this->RSA_private_encrypt(err('面值添加失败'));
            }
        }
        else {
            return $this->RSA_private_encrypt(succ('面值已经添加到数据库'));
        }
    }

    /**
     * 判断游戏是否支持
     * @return string
     */
    public function support()
    {
        $productIdentifier = $this->param('productIdentifier'); // 包名

        if (empty($productIdentifier))
        {
            return $this->RSA_private_encrypt(err('productIdentifier length is 0'));
        }

        $map = [
            ['status', '=', 1],
            ['productIdentifier', '=', $productIdentifier]
        ];

        $game = Game::where($map)->first();

        if ($game)
        {

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
}
