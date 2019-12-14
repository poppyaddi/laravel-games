<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserInfo;
use Illuminate\Http\Request;

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
}
